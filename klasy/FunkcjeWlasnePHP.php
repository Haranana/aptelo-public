<?php

class FunkcjeWlasnePHP {

  // funkcja zastepujaca strtotime()
  public static function my_strtotime( $wartosc ) {

      if ( $wartosc == '0000-00-00 00:00:00' ) {
           $wartosc = '1970-01-01 00:00:00';
      }
      if ( $wartosc == '0000-00-00' ) {
           $wartosc = '1970-01-01';
      }
      if ( $wartosc == '' ) {
           $wartosc = '1970-01-01 00:00:00';
      }

      return strtotime($wartosc);

  }
  
  // funkcja zastepujaca htmlentities()
  public static function my_htmlentities( $wartosc ) {
    
      if ( !empty($wartosc) ) {
          
           return htmlentities($wartosc, ENT_QUOTES, "UTF-8");
           
      } else {
        
          return '';
          
      }      
      
  }

  // Funkcja zastepujaca strftime
  public static function my_strftime(string $format, $timestamp = null, ?string $locale = null): string {

    if (null === $timestamp) {
        $timestamp = new \DateTime;
    } elseif (is_numeric($timestamp)) {
        $timestamp = date_create('@' . $timestamp);
        if ($timestamp) {
            $timestamp->setTimezone(new \DateTimezone(date_default_timezone_get()));
        }
    } elseif (is_string($timestamp)) {
        $timestamp = date_create($timestamp);
    }

    if (!($timestamp instanceof \DateTimeInterface)) {
        throw new \InvalidArgumentException('$timestamp argument is neither a valid UNIX timestamp, a valid date-time string or a DateTime object.');
    }

    $locale = substr((string) $locale, 0, 5);

    $intl_formats = [
        '%a' => 'EEE',	// An abbreviated textual representation of the day	Sun through Sat
        '%A' => 'EEEE',	// A full textual representation of the day	Sunday through Saturday
        '%b' => 'MMM',	// Abbreviated month name, based on the locale	Jan through Dec
        '%B' => 'MMMM',	// Full month name, based on the locale	January through December
        '%h' => 'MMM',	// Abbreviated month name, based on the locale (an alias of %b)	Jan through Dec
    ];

    $intl_formatter = function (\DateTimeInterface $timestamp, string $format) use ($intl_formats, $locale) {
        $tz = $timestamp->getTimezone();
        $date_type = \IntlDateFormatter::FULL;
        $time_type = \IntlDateFormatter::FULL;
        $pattern = '';

        if ($format == '%c') {
            $date_type = \IntlDateFormatter::LONG;
            $time_type = \IntlDateFormatter::SHORT;
        } elseif ($format == '%x') {
            $date_type = \IntlDateFormatter::SHORT;
            $time_type = \IntlDateFormatter::NONE;
        } elseif ($format == '%X') {
            $date_type = \IntlDateFormatter::NONE;
            $time_type = \IntlDateFormatter::MEDIUM;
        } else {
            $pattern = $intl_formats[$format];
        }

        return (new \IntlDateFormatter($locale, $date_type, $time_type, $tz, null, $pattern))->format($timestamp);
    };

    $translation_table = [
        // Day
        '%a' => $intl_formatter,
        '%A' => $intl_formatter,
        '%d' => 'd',
        '%e' => function ($timestamp) {
            return sprintf('% 2u', $timestamp->format('j'));
        },
        '%j' => function ($timestamp) {
            // Day number in year, 001 to 366
            return sprintf('%03d', $timestamp->format('z')+1);
        },
        '%u' => 'N',
        '%w' => 'w',

        // Week
        '%U' => function ($timestamp) {
            // Number of weeks between date and first Sunday of year
            $day = new \DateTime(sprintf('%d-01 Sunday', $timestamp->format('Y')));
            return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
        },
        '%V' => 'W',
        '%W' => function ($timestamp) {
            // Number of weeks between date and first Monday of year
            $day = new \DateTime(sprintf('%d-01 Monday', $timestamp->format('Y')));
            return sprintf('%02u', 1 + ($timestamp->format('z') - $day->format('z')) / 7);
        },

        // Month
        '%b' => $intl_formatter,
        '%B' => $intl_formatter,
        '%h' => $intl_formatter,
        '%m' => 'm',

        // Year
        '%C' => function ($timestamp) {
            // Century (-1): 19 for 20th century
            return floor($timestamp->format('Y') / 100);
        },
        '%g' => function ($timestamp) {
            return substr($timestamp->format('o'), -2);
        },
        '%G' => 'o',
        '%y' => 'y',
        '%Y' => 'Y',

        // Time
        '%H' => 'H',
        '%k' => function ($timestamp) {
            return sprintf('% 2u', $timestamp->format('G'));
        },
        '%I' => 'h',
        '%l' => function ($timestamp) {
            return sprintf('% 2u', $timestamp->format('g'));
        },
        '%M' => 'i',
        '%p' => 'A', // AM PM (this is reversed on purpose!)
        '%P' => 'a', // am pm
        '%r' => 'h:i:s A', // %I:%M:%S %p
        '%R' => 'H:i', // %H:%M
        '%S' => 's',
        '%T' => 'H:i:s', // %H:%M:%S
        '%X' => $intl_formatter, // Preferred time representation based on locale, without the date

        // Timezone
        '%z' => 'O',
        '%Z' => 'T',

        // Time and Date Stamps
        '%c' => $intl_formatter,
        '%D' => 'm/d/Y',
        '%F' => 'Y-m-d',
        '%s' => 'U',
        '%x' => $intl_formatter,
    ];

    $out = preg_replace_callback('/(?<!%)(%[a-zA-Z])/', function ($match) use ($translation_table, $timestamp) {
        if ($match[1] == '%n') {
            return "\n";
        } elseif ($match[1] == '%t') {
            return "\t";
        }

        if (!isset($translation_table[$match[1]])) {
            throw new \InvalidArgumentException(sprintf('Format "%s" is unknown in time format', $match[1]));
        }

        $replace = $translation_table[$match[1]];

        if (is_string($replace)) {
            return $timestamp->format($replace);
        } else {
            return $replace($timestamp, $match[1]);
        }
    }, $format);

    $out = str_replace('%%', '%', $out);
    return $out;
  }

}

?>
