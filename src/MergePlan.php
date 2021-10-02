<?php

declare(strict_types=1);

namespace Yiisoft\Config;

/**
 * @internal
 */
final class MergePlan
{
    /**
     * @psalm-var array<string, array<string, array<string, string[]>>>
     */
    private array $mergePlan;

    /**
     * @psalm-param array<string, array<string, array<string, string[]>>> $mergePlan
     */
    public function __construct(array $mergePlan = [])
    {
        $this->mergePlan = $mergePlan;
    }

    /**
     * Adds an item to the merge plan.
     *
     * @param string $file The config file.
     * @param string $package The package name.
     * @param string $group The group name.
     * @param string $environment The environment name.
     */
    public function add(
        string $file,
        string $package,
        string $group,
        string $environment = Options::DEFAULT_ENVIRONMENT
    ): void {
        $this->mergePlan[$environment][$group][$package][] = $file;
    }

    /**
     * Adds a multiple items to the merge plan.
     *
     * @param string[] $files The config files.
     * @param string $package The package name.
     * @param string $group The group name.
     * @param string $environment The environment name.
     */
    public function addMultiple(
        array $files,
        string $package,
        string $group,
        string $environment = Options::DEFAULT_ENVIRONMENT
    ): void {
        $this->mergePlan[$environment][$group][$package] = $files;
    }

    /**
     * Returns the merge plan group.
     *
     * @param string $group The group name.
     * @param string $environment The environment name.
     *
     * @return array<string, string[]>
     */
    public function getGroup(string $group, string $environment = Options::DEFAULT_ENVIRONMENT): array
    {
        return $this->mergePlan[$environment][$group] ?? [];
    }

    public function replace(
        string $file,
        string $package,
        string $packageReplace,
        string $group,
        string $replaceFile,
        string $environment = Options::DEFAULT_ENVIRONMENT
    ): void {
        if (!isset($this->mergePlan[$environment][$group][$package])) {
            return;
        }
        foreach ($this->mergePlan[$environment][$group][$package] as $index => $currentFile) {
            if ($currentFile === $file) {
                if (!$this->hasConfig($replaceFile, $packageReplace, $group, $environment)) {
                    $this->add($replaceFile, $packageReplace, $group, $environment);
                }

                unset($this->mergePlan[$environment][$group][$package][$index]);

                if ($this->mergePlan[$environment][$group][$package] === []) {
                    unset($this->mergePlan[$environment][$group][$package]);
                }
            }
        }
    }

    public function hasConfig(
        string $file,
        string $package,
        string $group,
        string $environment = Options::DEFAULT_ENVIRONMENT
    ): bool {
        $files = $this->mergePlan[$environment][$group][$package] ?? [];

        foreach ($files as $configFile) {
            if ($file === $configFile) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the merge plan as an array.
     *
     * @psalm-return array<string, array<string, array<string, string[]>>>
     */
    public function toArray(): array
    {
        return $this->mergePlan;
    }

    /**
     * Checks whether the group exists in the merge plan.
     *
     * @param string $group The group name.
     * @param string $environment The environment name.
     *
     * @return bool Whether the group exists in the merge plan.
     */
    public function hasGroup(string $group, string $environment = Options::DEFAULT_ENVIRONMENT): bool
    {
        return isset($this->mergePlan[$environment][$group]);
    }

    /**
     * Checks whether the environment exists in the merge plan.
     *
     * @param string $environment The environment name.
     *
     * @return bool Whether the environment exists in the merge plan.
     */
    public function hasEnvironment(string $environment): bool
    {
        return isset($this->mergePlan[$environment]);
    }
}
