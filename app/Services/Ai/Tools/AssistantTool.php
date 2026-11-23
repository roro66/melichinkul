<?php

namespace App\Services\Ai\Tools;

abstract class AssistantTool
{
    abstract public function name(): string;

    abstract public function description(): string;

    /** @return array<string, mixed> */
    abstract public function parameters(): array;

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    abstract public function run(array $args): array;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => $this->description(),
                'parameters' => $this->parameters(),
            ],
        ];
    }
}
