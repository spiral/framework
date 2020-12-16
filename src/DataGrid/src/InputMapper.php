<?php

declare(strict_types=1);

namespace Spiral\DataGrid;

class InputMapper implements InputMapperInterface
{
    /** @var InputInterface */
    private $input;

    /** @var array */
    private $mapping;

    /** @var array */
    private $mapped = [];

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function withInput(InputInterface $input): InputMapperInterface
    {
        $mapper = clone $this;
        $mapper->input = $input;
        $mapper->mapped = [];

        return $mapper;
    }

    public function hasOption(string $option): bool
    {
        $mapped = $this->map($option);
        if (!isset($mapped[$option])) {
            return $this->input->hasValue($option);
        }

        $paths = (array)$mapped[$option][0];

        $firstPath = array_shift($paths);
        if (!$this->input->hasValue($firstPath)) {
            return false;
        }

        if (empty($paths)) {
            return true;
        }

        $data = $this->input->getValue($firstPath);
        foreach ($paths as $path) {
            if (!is_array($data)) {
                return false;
            }

            if (!isset($data[$path])) {
                return false;
            }

            $data = $data[$path];
        }

        return true;
    }

    public function getOption(string $option)
    {
        $mappedOptions = $this->map($option);

        if (!isset($mappedOptions)) {
            return $this->input->getValue($option);
        }

        $output = [];
        foreach ($mappedOptions as $mappedOption) {
            $paths = (array)$mappedOption[0];

            $firstPath = array_shift($paths);
            if (!$this->input->getValue($firstPath)) {
                return false;
            }

            $data = $this->input->getValue($firstPath);
            $unmappedData = $data;
            if (!empty($paths) && is_array($unmappedData)) {
                dump(compact('unmappedData','paths'));
                unset($unmappedData[$paths[0]]);
                dump(compact('unmappedData'));
            }

            foreach ($paths as $path) {
                $data = $data[$path];
            }

            $result = $data;
            foreach ((array)$mappedOption[1] as $path) {
                $result = [$path => $result];
            }
            $output[] = $result;
            if (is_array($unmappedData)) {
                $output[] = $unmappedData;
            }
        }

        return !empty($output) ? array_merge(...$output) : [];
    }

    protected function map(string $option): array
    {
        if (isset($this->mapped[$option])) {
            return $this->mapped[$option];
        }

        $result = [];
        foreach ($this->mapping as $from => $to) {
            $to = explode('.', $to);
            if (isset($to[0]) && $to[0] === $option) {
                array_shift($to);
                $result[] = [explode('.', $from), array_reverse($to)];
            }
        }

        $this->mapped[$option] = $result;
        return $result;
    }
}
