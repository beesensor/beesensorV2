<?php
Class Format {
    public static function dateEnEs($stDate) {
        $p = explode("-", $stDate);
        return $p[2]."/".$p[1]."/".$p[0];
    }
    public static function dateEsEn($stDate) {
        $p = explode("/", $stDate);
        return $p[2]."-".$p[1]."-".$p[0];
    }

    public static function dateTimeEnEs($stDateTime) {
        $p = explode(" ", $stDateTime);
        return Format::dateEnEs($p[0])." ".$p[1];
    }

    public static function dateTimeEsEn($stDateTime) {
        $p = explode(" ", $stDateTime);
        return Format::dateEsEn($p[0])." ".$p[1];
    }
}