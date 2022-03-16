<?php

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

class AveragePostNumberPerUser extends AbstractCalculator
{
    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private array $authors = [];

    /**
     * @var int
     */
    private int $postCount = 0;

    /**
     * @param SocialPostTo $postTo
     * @return void
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $authorId = $postTo->getAuthorId();

        if (!isset($this->authors[$authorId])) {
            $this->authors[$authorId] = true;
        }
        $this->postCount++;
    }

    /**
     * @return StatisticsTo
     */
    protected function doCalculate(): StatisticsTo
    {
        $authorsTotal = count($this->authors);
        $value = $authorsTotal > 0
            ? $this->postCount / $authorsTotal
            : 0;

        return (new StatisticsTo())->setValue(round($value,2));
    }

}