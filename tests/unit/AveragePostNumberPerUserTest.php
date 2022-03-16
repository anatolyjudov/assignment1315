<?php

declare(strict_types = 1);

namespace Tests\unit;

use DateTime;
use PHPUnit\Framework\TestCase;
use SocialPost\Dto\SocialPostTo;
use SocialPost\Hydrator\FictionalPostHydrator;
use Statistics\Calculator\AveragePostNumberPerUser;
use Statistics\Dto\ParamsTo;
use Statistics\Enum\StatsEnum;

/**
 * Class ATestTest
 *
 * @package Tests\unit
 */
class AveragePostNumberPerUserTest extends TestCase
{
    public function testInstantiable(): void
    {
        $className = AveragePostNumberPerUser::class;
        $apnpu = new $className();
        $this->assertIsObject($apnpu);
        $this->assertInstanceOf($className, $apnpu, "Can't instantiate AveragePostNumberPerUser class");
    }

    /**
     * @dataProvider dataAccumulateDataProvider
     */
    public function testAccumulateData(ParamsTo $params, SocialPostTo $postTo, bool $assertInThisMonth): void
    {
        $apnpu = (new AveragePostNumberPerUser())
            ->setParameters($params);

        $apnpu->accumulateData($postTo);
        $statistics = $apnpu->calculate();

        $this->assertTrue(
            $statistics->getValue() == ($assertInThisMonth ? 1 : 0),
            'Wrong dated post accumulated'
        );
    }

    public function testCalculate()
    {
        $postsData = json_decode(file_get_contents("/app/tests/data/posts-for-calculation.json"), true);
        $params = $this->buildParams($postsData['month']);
        $apnpu = (new AveragePostNumberPerUser())
            ->setParameters($params);
        $hydrator = new FictionalPostHydrator();
        foreach ($postsData['posts'] as $postData) {
            $apnpu->accumulateData($hydrator->hydrate($postData));
        }
        $statistics = $apnpu->calculate();
        $this->assertTrue(
            $statistics->getValue() == $postsData['assert_average_per_user'],
            'Calculation is wrong'
        );
    }

    public function dataAccumulateDataProvider(): array
    {
        $postsData = json_decode(file_get_contents("/app/tests/data/posts-for-calculation.json"), true);
        $params = $this->buildParams($postsData['month']);
        $hydrator = new FictionalPostHydrator();
        $data = [];
        foreach ($postsData['posts'] as $postData) {
            $data[] = [$params, $hydrator->hydrate($postData), $postData['assert_in_this_month']];
        }
        return $data;
    }

    protected function buildParams(string $month): ParamsTo
    {
        $date = DateTime::createFromFormat('F, Y', $month);
        if (false === $date) {
            $date = new DateTime();
        }
        $startDate = (clone $date)->modify('first day of this month');
        $endDate   = (clone $date)->modify('last day of this month');
        return (new ParamsTo())
            ->setStatName(StatsEnum::AVERAGE_POST_NUMBER_PER_USER)
            ->setStartDate($startDate)
            ->setEndDate($endDate);
    }
}
