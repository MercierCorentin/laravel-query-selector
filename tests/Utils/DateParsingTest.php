<?php

namespace NastuzziSamy\Laravel\Tests\Utils;

use NastuzziSamy\Laravel\Utils\DateParsing;
use PHPUnit\Framework\TestCase;

class DateParsingTest extends TestCase {
    
    /**
     * Test DateParsing::parse
     * @return void
     * 
     */
    public function testParseWithoutFormat(){
        $carbonDate = DateParsing::parse("1972-05-07");

        // Assertions
        $this->assertEquals(1972, $carbonDate->year);
        $this->assertEquals(5, $carbonDate->month);
        $this->assertEquals(7, $carbonDate->day);
    }

    /**
     * Test DateParsing::parse with different date format
     * @return void
     * 
     */
    public function testParseWithoutFormat2(){
        $carbonDate = DateParsing::parse("2058/12/31");

        // Assertions
        $this->assertEquals(2058, $carbonDate->year);
        $this->assertEquals(12, $carbonDate->month);
        $this->assertEquals(31, $carbonDate->day);
    }

    /**
     * Test DateParsing::parse with timestamp format
     * @return void
     * 
     */
    public function testParseFromTimestamp(){
        $timestamp = 60*60*24*4 + 45; // Four days + 45 seconds
        $carbonDate = DateParsing::parse($timestamp, "timestamp");

        // Assertions
        $this->assertEquals(1970, $carbonDate->year);
        $this->assertEquals(01, $carbonDate->month);
        $this->assertEquals(05, $carbonDate->day);
        $this->assertEquals(45, $carbonDate->second);
    }

    /**
     * Test DateParsing::parse with non trivial format
     * @return void
     * 
     */
    public function testParseFromFormat(){
        $carbonDate = DateParsing::parse("18042020", "dmY");
        
        // Assertions
        $this->assertEquals(2020, $carbonDate->year);
        $this->assertEquals(04, $carbonDate->month);
        $this->assertEquals(18, $carbonDate->day);
    }


    /**
     * Test DateParsingException without format
     * @return void
     * 
     */
    public function testParsingException(){
        $this->expectExceptionMessage("The given date can not be parsed and recognized. Try using YYYY-mm-dd format date or give format in second argument");

        DateParsing::parse("18042020");
        
    }

    /**
     * Test DateParsingException with format
     * @return void
     * 
     */
    public function testParsingExceptionWithFormat(){
        $this->expectExceptionMessage("The given date can not be parsed and recognized. Try checking your format");
        DateParsing::parse("18042020", "Y-m-d");
    }

    /**
     * Start of DateParsing::interval tests
     * 
     */

     /**
      * Test DateParsing::interval method
      * @depends testParseWithoutFormat
      * @depends testParseWithoutFormat2
      * @depends testParseFromTimestamp
      * @depends testParseFromFormat
      * @depends testParsingException
      * @depends testParsingExceptionWithFormat
      * @return void
      */
    public function testInterval(){
        $interval = DateParsing::interval("2020-05-20", "2020-06-12");
        
        // Assertions
        // First value
        $this->assertEquals(2020, $interval[0]->year);
        $this->assertEquals(05, $interval[0]->month);
        $this->assertEquals(20, $interval[0]->day);

        // Second value
        $this->assertEquals(2020, $interval[1]->year);
        $this->assertEquals(06, $interval[1]->month);
        $this->assertEquals(12, $interval[1]->day);
    }

    /**
      * Test DateParsing::interval method with wrong interval
      * @depends testParseWithoutFormat
      * @depends testParseWithoutFormat2
      * @depends testParseFromTimestamp
      * @depends testParseFromFormat
      * @depends testParsingException
      * @depends testParsingExceptionWithFormat
      * @return void
      */
      public function testWrongInterval(){
        $this->expectExceptionMessage("Incorrect interval, the second date must happen after the first one");
        DateParsing::interval("2020-06-12", "2020-05-20");
    }

        /**
      * Test DateParsing::interval method with wrong interval
      * @depends testParseWithoutFormat
      * @depends testParseWithoutFormat2
      * @depends testParseFromTimestamp
      * @depends testParseFromFormat
      * @depends testParsingException
      * @depends testParsingExceptionWithFormat
      * @return void
      */
      public function testIntervalSame(){
        $this->expectExceptionMessage("An interval must contain two different dates");
        $interval = DateParsing::interval("2020-06-12", "2020-06-12");
    }


    /**
      * Test DateParsing::interval method with wrong interval
      * @depends testParseWithoutFormat
      * @depends testParseWithoutFormat2
      * @depends testParseFromTimestamp
      * @depends testParseFromFormat
      * @depends testParsingException
      * @depends testParsingExceptionWithFormat
      * @return void
      */
      public function testIntervalSameAllowed(){
        $interval = DateParsing::interval("2020-06-12", "2020-06-12", null, null, true);

        $this->assertEquals(2020, $interval[1]->year);
        $this->assertEquals(06, $interval[1]->month);
        $this->assertEquals(12, $interval[1]->day);
        $this->assertEquals(2020, $interval[0]->year);
        $this->assertEquals(06, $interval[0]->month);
        $this->assertEquals(12, $interval[0]->day);
    }

        /**
      * Test DateParsing::interval method with wrong interval
      * @depends testParseWithoutFormat
      * @depends testParseWithoutFormat2
      * @depends testParseFromTimestamp
      * @depends testParseFromFormat
      * @depends testParsingException
      * @depends testParsingExceptionWithFormat
      * @return void
      */
      public function testIntervalDifferentSameAllowed(){
        $interval = DateParsing::interval("2020-05-20", "2020-06-12", null, null, true);
        
        // Assertions
        // First value
        $this->assertEquals(2020, $interval[0]->year);
        $this->assertEquals(05, $interval[0]->month);
        $this->assertEquals(20, $interval[0]->day);

        // Second value
        $this->assertEquals(2020, $interval[1]->year);
        $this->assertEquals(06, $interval[1]->month);
        $this->assertEquals(12, $interval[1]->day);
    }
}