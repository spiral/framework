<?php

declare(strict_types=1);

namespace Symplify\ComposerJsonManipulator;

use Symplify\ComposerJsonManipulator\FileSystem\JsonFileManager;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ComposerJsonFactory
{
    /**
     * @var JsonFileManager
     */
    private $jsonFileManager;

    public function __construct(JsonFileManager $jsonFileManager)
    {
        $this->jsonFileManager = $jsonFileManager;
    }

    public function createFromFileInfo(SmartFileInfo $smartFileInfo): ComposerJson
    {
        $jsonArray = $this->jsonFileManager->loadFromFilePath($smartFileInfo->getRealPath());

        $composerJson = $this->createFromArray($jsonArray);
        $composerJson->setOriginalFileInfo($smartFileInfo);

        return $composerJson;
    }

    public function createFromFilePath(string $filePath): ComposerJson
    {
        $jsonArray = $this->jsonFileManager->loadFromFilePath($filePath);

        $composerJson = $this->createFromArray($jsonArray);
        $fileInfo = new SmartFileInfo($filePath);
        $composerJson->setOriginalFileInfo($fileInfo);

        return $composerJson;
    }

    public function createFromArray(array $jsonArray): ComposerJson
    {
        $composerJson = new ComposerJson($jsonArray);

        $orderedKeys = array_keys($jsonArray);

        $composerJson->setOrderedKeys($orderedKeys);

        return $composerJson;
    }
}
