<?php
/**
 * @var $queryData stdObject - composed by the EventList and passed into
 * this file.
 * @var $this \Concrete\Package\Schedulizer\Src\EventList
 * @note: this file gets require'd in a method of the EventList object,
 * and as such, $this is treated as it would be normally within the
 * context of an instance method (the EventList class). That is to say,
 * that by requiring this file in a method, its treated like copying/pasting
 * this code directly within the method.
 * @todo: why does this installation of the package fail w/out this check? funky autoloading of the src/ directory?
 */
if( !( $this instanceof \Concrete\Package\Schedulizer\Src\EventList ) ){
    return;
}

// Variables passed into the query
$startDateString    = $queryData->startDTO->format('Y-m-d');
$queryDaySpan       = $queryData->queryDaySpan;
$limitPerDay        = ($queryData->limitPerDay >= 1) ? " LIMIT {$queryData->limitPerDay}" : '';
$selectColumns      = join(',', array_keys($queryData->selectableColumns));

/**
 * Is eventGrouping happening? Then add to the select columns.
 * @note: THIS AUTOMATICALLY MAKES THE occurrences COLUMN AVAILABLE,
 * WHICH IS A COUNT OF THE TIMES THE EVENT HAPPENS (it replaces the actual 'occurrences'
 * column with count() AS occurrences)
 */
$groupByClause = '';
if( $queryData->doEventGrouping === true ){
    $selectColumns = str_replace('occurrences', 'count(isSynthetic) AS occurrences', $selectColumns);
    $groupByClause = " GROUP BY eventID ";
}

/**
 * Restriction on internal join.
 */
if( !empty($queryData->calendarIDs) ){
    $restrictor = sprintf(
        "sev.calendarID IN (%s) AND (DATE(sevt.startUTC) < DATE('%s'))",
        join(',', $queryData->calendarIDs),
        $queryData->endDTO->format('Y-m-d')
    );
}else{
    $restrictor = sprintf(
        "(DATE(sevt.startUTC) < DATE('%s'))",
        $queryData->endDTO->format('Y-m-d')
    );
}


/**
 * Are we also restricting by eventIDs?
 */
if( !empty($queryData->eventIDs) ){
    $restrictor .= sprintf(
        " AND sev.id IN (%s)",
        join(',', $queryData->eventIDs)
    );
}

/**
 * Full text search? This is NOT part of the restrictor, but instead gets run on
 * the $latestEventRecords join below.
 */
$fullTextSearch = '';
if( !empty($queryData->fullTextSearch) ){
    $fullTextSearch = sprintf(
        " AND (MATCH (_eventVersions.title, _eventVersions.description) AGAINST ('%s'))",
        $queryData->fullTextSearch
    );
}

/**
 * This is effectively the "inner-most" query on top of which everything is joined and filtered
 * against; this is responsible for getting the event and LATEST version info
 */
$latestEventRecords = <<<SQL
    SELECT
        _events.id,
        _events.createdUTC,
        _events.modifiedUTC,
        _events.calendarID,
        _events.ownerID,
        _events.pageID,
        _versionInfo.versionID,
        _versionInfo.title,
        _versionInfo.description,
        _versionInfo.useCalendarTimezone,
        _versionInfo.timezoneName,
        _versionInfo.eventColor,
        _versionInfo.fileID
    FROM SchedulizerEvent _events LEFT JOIN (
      SELECT _eventVersions.* FROM SchedulizerEventVersion _eventVersions
      INNER JOIN (
         SELECT eventID, MAX(versionID) AS highestVersionID FROM SchedulizerEventVersion GROUP BY eventID
      ) _eventVersions2
      ON _eventVersions.eventID = _eventVersions2.eventID
      AND _eventVersions.versionID = _eventVersions2.highestVersionID
      $fullTextSearch
    ) AS _versionInfo ON _events.id = _versionInfo.eventID
SQL;

/**
 * If we're filtering by tags, do a join on the latestEventRecords to filter at that
 * level before any other outer joins occur higher up.
 */
if( !empty($queryData->tagIDs) ){
$tagIDs = join(',', $queryData->tagIDs);
$latestEventRecords .= <<<SQL
    JOIN SchedulizerTaggedEvents stag ON stag.eventID = _events.id AND stag.versionID = _versionInfo.versionID
    WHERE stag.eventTagID IN ($tagIDs) GROUP BY _events.id
SQL;
}

/**
 * Are we putting a limit on the total number of results that can be returned?
 */
$limitResultsClause = '';
if( $queryData->limitTotal >= 1 ){
    $limitResultsClause = sprintf("LIMIT %s", $queryData->limitTotal);
}

/**
 * This query is freaking atrocious - so break it out into a new file where we can
 * actually format and view it w/ proper indentation. Note, this is called by a method
 * in EventList, so the context of this file receives the variables declared in that
 * method.
 */
$sql = <<<SQL
    SELECT $selectColumns FROM (
        /* This level of select statements makes dynamic adjustments/calculations as necessary, which
          can THEN be queried in the final select statement above. */
        SELECT
            _synthesized._syntheticDate,
            /* Compute startUTC on the fly (ie. startUTC of the original event, but adjusted to the synthetic date */
            TIMESTAMP(_synthesized._syntheticDate, TIME(_events.startUTC)) AS computedStartUTC,
            /* If its weekly, convert here */
            (CASE WHEN
                  (_events.repeatTypeHandle = 'weekly') AND
                  (_synthesized._syntheticDate != DATE(CONVERT_TZ(TIMESTAMP(DATE(_synthesized._syntheticDate), TIME(_events.startUTC)), 'UTC', _events.derivedTimezone)))
                IS TRUE THEN
                  TIMESTAMP(_synthesized._syntheticDate, TIME(CONVERT_TZ(TIMESTAMP(DATE(_synthesized._syntheticDate), TIME(_events.startUTC)), 'UTC', _events.derivedTimezone)))
                ELSE
                  CONVERT_TZ(TIMESTAMP(DATE(_synthesized._syntheticDate), TIME(_events.startUTC)), 'UTC', _events.derivedTimezone)
            END) AS computedStartLocal,
            /* Compute the endUTC time */
            TIMESTAMPADD(MINUTE, TIMESTAMPDIFF(MINUTE,_events.startUTC,_events.endUTC), TIMESTAMP(_synthesized._syntheticDate, TIME(_events.startUTC))) AS computedEndUTC,
            /* Compute end time but localized */
            CONVERT_TZ(TIMESTAMPADD(MINUTE, TIMESTAMPDIFF(MINUTE,_events.startUTC,_events.endUTC), TIMESTAMP(_synthesized._syntheticDate, TIME(_events.startUTC))), 'UTC', _events.derivedTimezone) AS computedEndLocal,
            /* Get all other record data from subqueries */
            _events.*,
            /* Determine whether the record is synthetically generated */
            (CASE WHEN (_synthesized._syntheticDate != DATE(_events.startUTC)) IS TRUE THEN 1 ELSE 0 END) as isSynthetic
        FROM (
            /* Where the magic happens for dynamically generating a series of dates into the future
             against which we can join event records. */
            SELECT DATE('$startDateString' + INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY) AS _syntheticDate
            FROM (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as a
            CROSS JOIN (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as b
            CROSS JOIN (select 0 as a union all select 1 union all select 2 union all select 3 union all select 4 union all select 5 union all select 6 union all select 7 union all select 8 union all select 9) as c
            LIMIT $queryDaySpan
        ) AS _synthesized
        JOIN (
            SELECT
              sev.id AS eventID,
              sev.versionID AS versionID,
              sec.id AS calendarID,
              sevt.id AS eventTimeID,
              sev.title,
              sev.description,
              sev.useCalendarTimezone,
              (CASE WHEN (sev.useCalendarTimezone = 1) IS TRUE THEN sec.defaultTimezone ELSE sev.timezoneName END) as derivedTimezone,
              sev.eventColor,
              sev.ownerID,
              sev.fileID,
              sev.pageID,
              sevt.startUTC,
              sevt.endUTC,
              sevt.isOpenEnded,
              sevt.isAllDay,
              sevt.isRepeating,
              sevt.repeatTypeHandle,
              sevt.repeatEvery,
              sevt.repeatIndefinite,
              sevt.repeatEndUTC,
              sevt.repeatMonthlyMethod,
              sevt.repeatMonthlySpecificDay,
              sevt.repeatMonthlyOrdinalWeek,
              sevt.repeatMonthlyOrdinalWeekday,
              sevtwd.repeatWeeklyday
            FROM SchedulizerCalendar sec
              /*
                Previously: JOIN SchedulizerEvent sev ON sev.calendarID = sec.id
                ---
                Before versioning, this just did a join against event records; but now
                we have to use a subquery and join against those results so we make sure
                we're using only the latest event version.
              */
              JOIN ($latestEventRecords) sev ON sev.calendarID = sec.id
              /*
                Previously: JOIN SchedulizerEventTime sevt ON sevt.eventID = sev.id
                ---
                Now join event time records against the versioned event record; so we have one
                row for each event with the latest version data, then we join those with one
                or more event times; so if Event:1,Version:2 has three EventTimes, it'll generate
                3 joined rows, whereas all the event data is the same, but different time settings
                NOTE: eventTimes are versioned along w/ EventVersions
              */
              JOIN SchedulizerEventTime sevt ON sevt.eventID = sev.id AND sevt.versionID = sev.versionID
              /*
               Join weekday data (if applicable) against the previous joins
               */
              LEFT JOIN SchedulizerEventTimeWeekdays sevtwd ON sevtwd.eventTimeID = sevt.id

            /* Filter the results from all the internal joins */
            WHERE ($restrictor) ORDER BY sevt.startUTC asc $limitPerDay
        ) AS _events
        WHERE(_events.isRepeating = 1
            AND (_events.repeatIndefinite = 1 OR (_synthesized._syntheticDate <= _events.repeatEndUTC AND _events.repeatIndefinite = 0))
            AND (DATE(_events.startUTC) <= _synthesized._syntheticDate)
            AND (_events.eventTimeID NOT IN (SELECT _nullifiers.eventTimeID FROM SchedulizerEventTimeNullify _nullifiers WHERE _synthesized._syntheticDate = DATE(_nullifiers.hideOnDate)))
            AND (
              (_events.repeatTypeHandle = 'daily'
                AND (DATEDIFF(_synthesized._syntheticDate,_events.startUTC) % _events.repeatEvery = 0 )
              )

              OR (_events.repeatTypeHandle = 'weekly'
                 AND (_events.repeatWeeklyday = DAYOFWEEK(_synthesized._syntheticDate))
                 AND (CEIL(DATEDIFF(_events.startUTC, _synthesized._syntheticDate) / 7 ) % _events.repeatEvery = 0)
              )

              OR ((_events.repeatTypeHandle = 'monthly' AND _events.repeatMonthlyMethod = 'specific')
                 AND (_events.repeatMonthlySpecificDay = DAYOFMONTH(_synthesized._syntheticDate))
                 AND ((MONTH(_synthesized._syntheticDate) - MONTH(_events.startUTC)) % _events.repeatEvery = 0)
              )

              OR ((_events.repeatTypeHandle = 'monthly' AND _events.repeatMonthlyMethod = 'ordinal')
                 AND ((DATE_ADD(DATE_SUB(LAST_DAY(_synthesized._syntheticDate), INTERVAL DAY(LAST_DAY(_synthesized._syntheticDate)) -1 DAY), INTERVAL (((_events.repeatMonthlyOrdinalWeekday + 7) - DAYOFWEEK(DATE_SUB(LAST_DAY(_synthesized._syntheticDate), INTERVAL DAY(LAST_DAY(_synthesized._syntheticDate)) -1 DAY))) % 7) + ((_events.repeatMonthlyOrdinalWeek * 7) -7) DAY)) = _synthesized._syntheticDate)
                 AND ((MONTH(_synthesized._syntheticDate) - MONTH(_events.startUTC)) % _events.repeatEvery = 0)
              )

              OR(_events.repeatTypeHandle = 'yearly'
                AND ((YEAR(_synthesized._syntheticDate) - YEAR(_events.startUTC)) % _events.repeatEvery = 0)
              )
            )
        )
        OR (
          (_events.isRepeating = 0 AND _synthesized._syntheticDate = DATE(_events.startUTC))
        )
    ) AS _eventList $groupByClause ORDER BY computedStartUTC $limitResultsClause;
SQL;

// Return the fully composed SQL query
return $sql;