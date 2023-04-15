<?php

namespace hehe;

use DateTime;
use DateTimeZone;

/**
 * 日期时间处理类
 */
class Date {
	const YEAR = 31536000;
	const MONTH = 2592000;
	const WEEK = 604800;
	const DAY = 86400;
	const HOUR = 3600;
	const MINUTE = 60;

	/**
	 * 获取两个时间戳之间相差的天数
	 */
	public static function getDaysDiff($local, $coming) {
		$local = strtotime(date('Y-m-d', $local));
		$coming = strtotime(date('Y-m-d', $coming));
		$daySeconds = 24 * 60 * 60;
		$diff = abs($coming - $local);
		$days = floor($diff / $daySeconds);
		return $days;
	}
}
