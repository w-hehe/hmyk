<?php

namespace hehe;

/**
 * 字符串
 */
class Str {




	/**
	 * 手机号码星号隐藏
	 */
	public static function phoneHidden($phone) {
		return substr_replace($phone, '****', 3, 4);
	}

	/**
	 * 友好显示距离
	 */
	public static function distance($distance) {
		return $distance < 1 ? floor($distance * 1000) . 'm' : (floor($distance * 10) / 10) . 'km';
	}

	/**
	 * 调用方法
	 * @param string $IdCard
	 * @return bool
	 */
	public static function verifyIdentity(string $IdCard) {
		if (strlen($IdCard) == 18) {
			return self::MethodIdCardChecksum18($IdCard);
		} elseif ((strlen($IdCard) == 15)) {
			$IdCard = self::MethodIdCard15to18($IdCard);
			return self::MethodIdCardChecksum18($IdCard);
		} else {
			return false;
		}
	}

	/**
	 * 计算身份证校验码，根据国家标准GB 11643-1999
	 * @param string $IdCardBase
	 * @return bool|mixed
	 */
	protected static function MethodIdCardVerifyNumber(string $IdCardBase) {
		if (strlen($IdCardBase) != 17) {
			return false;
		}
		//加权因子
		$factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
		//校验码对应值
		$verify_number_list = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
		$checksum = 0;
		for ($i = 0; $i < strlen($IdCardBase); $i++) {
			$checksum += substr($IdCardBase, $i, 1) * $factor[$i];
		}
		$mod = $checksum % 11;
		return $verify_number_list[$mod];
	}

	/**
	 * 将15位身份证升级到18位
	 * @param string $IdCard
	 * @return bool|string
	 */
	protected static function MethodIdCard15to18(string $IdCard) {
		if (strlen($IdCard) != 15) {
			return false;
		} else {
			// 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码
			if (array_search(substr($IdCard, 12, 3), ['996', '997', '998', '999']) !== false) {
				$IdCard = substr($IdCard, 0, 6) . '18' . substr($IdCard, 6, 9);
			} else {
				$IdCard = substr($IdCard, 0, 6) . '19' . substr($IdCard, 6, 9);
			}
		}
		$IdCard = $IdCard . self::MethodIdCardVerifyNumber($IdCard);
		return $IdCard;
	}

	// 18位身份证校验码有效性检查
	protected static function MethodIdCardChecksum18(string $IdCard) {
		if (strlen($IdCard) != 18) {
			return false;
		}
		$IdCardBase = substr($IdCard, 0, 17);
		if (self::MethodIdCardVerifyNumber($IdCardBase) != strtoupper(substr($IdCard, 17, 1))) {
			return false;
		} else {
			return true;
		}
	}



}
