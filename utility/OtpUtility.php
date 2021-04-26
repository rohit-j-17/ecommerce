<?php

class OtpUtility
{
    public static function generateOpt()
    {
        return null;
    }

    public static function generateNumericOTP()
    { 
        $n = 6;
        $generator = "1357902468";
        $otp = "";

        for ($i = 1; $i <= $n; $i++) {
            $otp .= substr($generator, (rand() % (strlen($generator))), 1);
        }
        return $otp;
    }
}
