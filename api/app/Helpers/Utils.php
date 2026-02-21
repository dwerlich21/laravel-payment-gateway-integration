<?php
/**
 * Created by PhpStorm.
 * User: werlich
 * Date: 13/01/20
 * Time: 17:50
 */

namespace App\Helpers;


class Utils
{


    public static function convertHtmlToWhatsApp($html)
    {
        // Remove as tags HTML, preservando o conteúdo
        $text = strip_tags($html, '<ul><ol><li><p><a>');

        // Substitui as tags de parágrafo por quebras de linha duplas
        $text = preg_replace('/<p>(.*?)<\/p>/is', "$1\n\n", $text);

        // Converte listas não ordenadas (<ul><li>) em lista com bullet points
        $text = preg_replace('/<ul>(.*?)<\/ul>/is', "$1", $text);
        $text = preg_replace('/<li>(.*?)<\/li>/is', "• $1\n", $text);

        // Converte listas ordenadas (<ol><li>) em lista numerada (opcional)
        // Como já temos uma lista com bullets, vamos remover a lista ordenada duplicada
        $text = preg_replace('/<ol>(.*?)<\/ol>/is', "", $text);

        // Preserva links (opcional, caso queira manter o formato <a href>)
        $text = preg_replace('/<a\s+href=["\'](.*?)["\'].*?>(.*?)<\/a>/is', "$2: $1", $text);

        // Remove quebras de linha extras e espaços duplicados
        $text = preg_replace('/[\n]{3,}/', "\n\n", $text);
        $text = preg_replace('/[ ]{2,}/', " ", $text);

        // Trim para remover espaços no início e fim
        $text = trim($text);
        $text = str_replace('&nbsp;', ' ', $text);

        return $text;
    }

    public static function onlyNumbers(string $str): array|string|null
    {
        return preg_replace("/[^0-9]/", "", $str);
    }

    public static function removeCpfMask($cpf)
    {
        return preg_replace('/[\.\-\_\/]/', '', $cpf);
    }

    public static function getAcessData(): array
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";
        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'Linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'Mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }
        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/OPR/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Chrome/i', $u_agent) && !preg_match('/Edge/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent) && !preg_match('/Edge/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } elseif (preg_match('/Edge/i', $u_agent)) {
            $bname = 'Edge';
            $ub = "Edge";
        } elseif (preg_match('/Trident/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        }
        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }
        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
        if ($version == null || $version == "") {
            $version = "?";
        }

        return [
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern,
            'ip' => $_SERVER['REMOTE_ADDR']
        ];
    }

    public static function generateToken(int $length = 50): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function getFloat($string): float
    {
        $number = str_replace(',', '.', $string);
        $float = floatval($number);
        if ($float == null || $float == '') throw new \Exception('Os campos devem ser preenchidos apenas por números!');

        return $float;
    }

    public static function moneyToFloat($string)
    {
        $number = str_replace('R$', '', $string);
        $number = str_replace('.', '', $string);
        $number = str_replace(',', '.', $string);
        $float = floatval(trim($number));
        if ($float == null || $float == '') throw new \Exception('Os campos devem ser preenchidos apenas por números!');

        return $float;
    }

    public static function mask($val, $mask)
    {
        $val = self::onlyNumbers($val ?? '');
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) $maskared .= $val[$k++];
            } else {
                if (isset($mask[$i])) $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }
}
