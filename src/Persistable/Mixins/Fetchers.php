<?php namespace Concrete\Package\Schedulizer\Src\Persistable\Mixins {

    use Concrete\Package\Schedulizer\Src\Persistable\DefinitionInspector;

    trait Fetchers {

        /**
         * Adhoc queries are different in that an executed PDOStatement will be returned
         * instead of having the results be transformed automatically into relevant objects.
         * Reason, for example, would be a delete statement.
         * @param callable $callback
         * @return mixed
         * @throws \Concrete\Package\Schedulizer\Src\Persistable\DefinitionInspectorException
         */
        public static function adhocQuery( \Closure $callback ){
            $instance   = new static();
            $definition = DefinitionInspector::parse($instance);
            $connection = \Core::make('SchedulizerDB');

            $statement = $callback( $connection, $definition->classDefinition()->table, $definition );
            $statement->execute();
            return $statement;
        }

        /**
         * Pass in an anonymous function that receives three arguments (a \PDOConnection object,
         * the table name, and optionally the full definition inspection), and pass back a
         * prepared statement. The statement will be executed and the results from the record
         * used to populate/reflect settings back onto the instance
         * @param callable $callback
         * @return $this|void
         */
        public static function fetchOneBy( \Closure $callback ){
            $instance   = new static();
            $definition = DefinitionInspector::parse($instance);
            $connection = \Core::make('SchedulizerDB');

            // This would let you call $this inside the closure; but doesn't work
            // when a closure is created within a static method/function
            //$callback   = \Closure::bind($callback, $instance, $instance);

            /** @var $statement \PDOStatement Must return a PREPARED (with bound values!) statement */
            $statement = $callback( $connection, $definition->classDefinition()->table, $definition );
            $statement->execute();
            $record = $statement->fetch(\PDO::FETCH_OBJ);
            if( ! $record ){ return; }
            $definition->reflectAllOntoInstance($instance, $record);
            $instance->onAfterFetch($record);
            return $instance;
        }

        /**
         * Same thing as above, except return multiple instances.
         * @param callable $callback
         * @return $this|void
         */
        public static function fetchMultipleBy( \Closure $callback ){
            $instance   = new static();
            $definition = DefinitionInspector::parse($instance);
            $connection = \Core::make('SchedulizerDB');

            // This would let you call $this inside the closure; but doesn't work
            // when a closure is created within a static method/function
            //$callback   = \Closure::bind($callback, $instance, $instance);

            /** @var $statement \PDOStatement Must return a PREPARED (with bound values!) statement */
            $statement = $callback( $connection, $definition->classDefinition()->table, $definition );
            $statement->execute();
            $records = $statement->fetchAll(\PDO::FETCH_OBJ);
            if( ! $records ){ return; }

            // Loop through all the records and turn into the entities, invoking onAfterFetch
            // each time
            return array_map(function( $result ) use ($definition){
                $instance = new static();
                $definition->reflectAllOntoInstance($instance, $result);
                $instance->onAfterFetch($result);
                return $instance;
            }, $records);
        }

    }

}