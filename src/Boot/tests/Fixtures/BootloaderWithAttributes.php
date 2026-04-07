<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Attribute\BootMethod;
use Spiral\Boot\Attribute\InitMethod;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;

final class BootloaderWithAttributes extends Bootloader
{
    // Init
    public function init(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    #[InitMethod(priority: -1024)]
    public function initMethodF(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    #[InitMethod(priority: 1024)]
    public function initMethodA(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    #[InitMethod(priority: 256)]
    public function initMethodC(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    #[InitMethod(priority: -256)]
    public function initMethodD(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    // Boot
    public function boot(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    #[BootMethod(priority: -3)]
    public function bootMethodF(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    #[BootMethod(priority: 3)]
    public function bootMethodA(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    #[BootMethod(priority: 1)]
    public function bootMethodC(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    #[BootMethod(priority: -1)]
    public function bootMethodD(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    #[InitMethod(priority: 512)]
    protected function initMethodB(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    #[BootMethod(priority: 2)]
    protected function bootMethodB(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    #[InitMethod(priority: -512)]
    private function initMethodE(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }

    #[BootMethod(priority: -2)]
    private function bootMethodE(BinderInterface $binder): void
    {
        $binder->bind(__FUNCTION__, SampleClass::class);
    }
}
