<?php

namespace App\NativeComponents\Concerns;

use Carbon\Carbon;

trait HandlesCalendar
{
  public static function daysInMonth(int $year, int $month): int
  {
    //return Carbon::create($year, $month, 1)->daysInMonth;
    return (int) date(
      't',
      mktime(0, 0, 0, $month, 1, $year)
    );
    /*return (int) date('t', strtotime(
      "{$year}-{$month}-01"
    ));*/
  }
  /**
   * Get the number of days in a Gregorian calendar month.
   */
  protected function daysInMonthA(int $month, int $year): int
  {
    return match ($month) {
      1, 3, 5, 7, 8, 10, 12 => 31,
      4, 6, 9, 11 => 30,
      2 => $this->isLeapYear($year) ? 29 : 28,
      default => throw new \InvalidArgumentException(
        'Month must be between 1 and 12.'
      ),
    };
  }

  /**
   * Determine whether the given year is a leap year.
   */
  protected function isLeapYear(int $year): bool
  {
    return $year % 400 === 0
      || ($year % 4 === 0 && $year % 100 !== 0);
  }
}
