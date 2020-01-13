<?php

declare(strict_types=1);

namespace AfishaCrawler\Entity;


use Webmozart\Assert\Assert;

class AgeRestriction
{
    protected $value;

    /**
     * AgeRestriction constructor.
     * @param string $restriction
     */
    public function __construct(string $restriction)
    {
        Assert::oneOf($restriction, ['0+', '6+', '12+', '16+', '18+']);
        $this->value = $restriction;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString()
    {
        return $this->getValue();
    }
}