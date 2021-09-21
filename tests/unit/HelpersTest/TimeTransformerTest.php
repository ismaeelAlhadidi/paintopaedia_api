<?php 

use PHPUnit\Framework\TestCase;
use App\Helpers\TimeTransformer;

class TimeTransformerTest extends TestCase {

    public function test_convert_to_before_how_much_format() {

        $justNow = Date('Y-m-d H:i:s', time());
        $this->assertEquals(TimeTransformer::beforeHowMuch($justNow), 'just now');

        $beforeSomeMinutes = Date('Y-m-d H:i:s', time() - (60*8));
        $this->assertEquals(TimeTransformer::beforeHowMuch($beforeSomeMinutes), '8 minutes ago');

        $beforeOneHour = Date('Y-m-d H:i:s', time() - (60*60));
        $this->assertEquals(TimeTransformer::beforeHowMuch($beforeOneHour), 'hour ago');

        $beforeFourHours = Date('Y-m-d H:i:s', time() - (60*60*4));
        $this->assertEquals(TimeTransformer::beforeHowMuch($beforeFourHours), '4 hours ago');

        $beforeOneDay = Date('Y-m-d H:i:s', time() - (60*60*24));
        $this->assertEquals(TimeTransformer::beforeHowMuch($beforeOneDay), 'day ago');

        $beforeThreeDays = Date('Y-m-d H:i:s', time() - (60*60*24*3));
        $this->assertEquals(TimeTransformer::beforeHowMuch($beforeThreeDays), '3 days ago');

        $beforeTwoWeeks = Date('Y-m-d H:i:s', time() - (60*60*24*7*2));
        $this->assertEquals(TimeTransformer::beforeHowMuch($beforeTwoWeeks), '2 weeks ago');

        $beforeOneMonth = Date('Y-m-d H:i:s', time() - (60*60*24*30));
        $this->assertEquals(TimeTransformer::beforeHowMuch($beforeOneMonth), '4 weeks ago');

        $beforeOneAndHalfMonth = Date('Y-m-d H:i:s', time() - (60*60*24*30*1.5));
        $this->assertEquals(TimeTransformer::beforeHowMuch($beforeOneAndHalfMonth), 'month ago');

        $beforeThreeMonths = Date('Y-m-d H:i:s', time() - (60*60*24*30*3));
        $this->assertEquals(TimeTransformer::beforeHowMuch($beforeThreeMonths), '3 months ago');

        $beforeOneYear = Date('Y-m-d H:i:s', time() - (60*60*24*30*13));
        $this->assertEquals(TimeTransformer::beforeHowMuch($beforeOneYear), 'year ago');

        $beforeThreeYears = Date('Y-m-d H:i:s', time() - (60*60*24*30*12*3));
        $this->assertEquals(TimeTransformer::beforeHowMuch($beforeThreeYears), '3 years ago');

        $beforeManyYears = Date('Y-m-d H:i:s', time() - (60*60*24*30*12*8));
        $this->assertEquals(TimeTransformer::beforeHowMuch($beforeManyYears), '8 years ago');

    }

}