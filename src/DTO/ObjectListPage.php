<?php

declare(strict_types=1);

namespace Petersons\D2L\DTO;

use Illuminate\Support\Collection;

abstract class ObjectListPage
{
    public function __construct(
        private string|null $nextUrl,
        private Collection $objects
    ) {
        $this->checkObjectInstance();
    }

    public function getNextUrl(): string|null
    {
        return $this->nextUrl;
    }

    public function getObjects(): Collection
    {
        return $this->objects;
    }

    private function checkObjectInstance(): void
    {
        foreach ($this->objects as $object) {
            if (!$this->checkIsInstanceOfExpectedClass($object)) {
                throw new \InvalidArgumentException();
            }
        }
    }

    abstract protected function checkIsInstanceOfExpectedClass(object $object): bool;
}
