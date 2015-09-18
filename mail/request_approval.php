<?php defined('C5_EXECUTE') or die("Access Denied.");
/** @var $eventObj \Concrete\Package\Schedulizer\Src\Event */
/** @var $userInfoObj \Concrete\Core\User\UserInfo */

$subject = 'Schedulizer :: Event Approval Request';
$hashKey = md5(sprintf('%s:approval_key', $eventObj->getTitle()));
/** HTML Body Start */
ob_start(); ?>

<html>
	<head>
		<title>Clinica.org Receipt</title>
		<style type="text/css">
			body {margin:0;padding:0;font-family:Arial;font-size:13px;font-weight:normal;line-height:120%;}
			body {-webkit-text-size-adjust:none;}
			table td {border-collapse:collapse;}
			h1, .h1 {text-transform:uppercase;padding-top:0;padding-bottom:10px;font-family:Arial;font-size:20px;font-weight:normal;line-height:100%;}
			p, .p {font-size:12px;line-height:130%;}
			blockquote, .blockquote {font-size:14px;}
            hr {border-left:0;border-right:0;border-bottom:0;border-top:1px solid #dddddd;height:1px;width:100%;}
		</style>
	</head>
	<body style="background-color:#f5f5f5;" leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<center>
			<br /><br />
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="height:100% !important;margin:0;padding:0;width:100% !important;">
				<tr>
					<td valign="top">
						<center>
							<h1 class="h1">Event Approval Request From <a href="mailto:<?php echo $userInfoObj->getUserEmail(); ?>"><?php echo $userInfoObj->getUserName(); ?></a></h1>
							<table cellpadding="0" cellspacing="0" width="600" style="background-color:#fff;border:1px solid #ccc;">
								<tr>
									<td valign="top">
										<table border="0" cellpadding="10" cellspacing="0" width="600">
											<tr>
												<td>
													<p class="p" style="background-color:#f1f1f1;font-size:17px;text-align:center;margin-top:0;margin-bottom:0;padding-left:15px;padding-right:15px;padding-bottom:10px;padding-top:10px;"><?php echo $eventObj; ?></p>
                                                    <p class="p"><strong>Description:</strong></p>
                                                    <?php echo $eventObj->getDescription(); ?>
                                                    <p class="p"><strong>Photo:</strong></p>
                                                    <p class="p" style="text-align:center;">
                                                        <?php $path = $eventObj->getImageThumbnailPath(); if(!empty($path)): ?>
                                                            <img src="<?php echo $path; ?>" />
                                                        <?php else: ?>
                                                            <span>No Image</span>
                                                        <?php endif; ?>
                                                    </p>
                                                    <hr />
                                                    <p class="p"><strong>Times: </strong></p>
                                                    <ul>
                                                        <?php $times = (array) $eventObj->getEventTimes(); foreach($times AS $timeObj): /** @var $timeObj \Concrete\Package\Schedulizer\Src\EventTime */ ?>
                                                            <li>
                                                                <strong>Starts:</strong> <?php echo $timeObj->getStartUTC()->setTimezone(new \DateTimeZone($eventObj->getTimezoneName()))->format('M d, Y H:i:s'); ?>,
                                                                <strong>Ends:</strong> <?php echo $timeObj->getEndUTC()->setTimezone(new \DateTimeZone($eventObj->getTimezoneName()))->format('M d, Y h:i:s'); ?>
                                                                <?php if( $timeObj->getIsRepeating() ){ ?>
                                                                    (repeating)
                                                                <?php } ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                    <hr />
                                                    <p class="p"><strong>Tags: </strong> <?php echo join(', ', $eventObj->getEventTags()); ?></p>
                                                    <p class="p"><strong>Categories: </strong> <?php echo join(', ', $eventObj->getEventCategories()); ?></p>
                                                    <hr />
                                                    <?php
                                                        $attrList = \Concrete\Package\Schedulizer\Src\Attribute\Key\SchedulizerEventKey::getList();
                                                        foreach($attrList AS $attrKeyObj){ ?>
                                                            <p class="p"><strong><?php echo $attrKeyObj->getAttributeKeyName(); ?>:</strong> <?php echo $eventObj->getAttribute($attrKeyObj->getAttributeKeyHandle(), 'display'); ?></p>
                                                    <?php } ?>
                                                    <hr />
                                                    <p class="p" style="text-align:center;color:#aaa;"><strong>Version #:</strong> <?php echo $eventObj->getVersionID(); ?> // <strong>Calendar:</strong> <a href="<?php echo BASE_URL . DIR_REL . '/dashboard/schedulizer/calendars/manage/' . $eventObj->getCalendarID(); ?>"><?php echo $eventObj->getCalendarObj(); ?></a></p>
												</td>
											</tr>
										</table>
									</td>
								</tr>
							</table>
						</center>
					</td>
				</tr>
			</table>
			<br /><br />
		</center>
	</body>
</html>

<?php $bodyHTML = ob_get_clean(); /** HTML Body End */