<?php

declare(strict_types=1);

namespace Framework\Console\Confirmation;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\Environment\AppEnvironment;
use Spiral\Console\Confirmation\ApplicationInProduction;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ApplicationInProductionTest extends TestCase
{
    #[DataProvider('notProductionEnvs')]
    public function testNotProductionEnvironmentShouldBeConfirmed(AppEnvironment $env): void
    {
        $confirmation = new ApplicationInProduction(
            $env,
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $this->assertTrue($confirmation->confirmToProceed());
    }

    public function testProductionEnvWithForceOptionShouldBeConfirmed(): void
    {
        $confirmation = new ApplicationInProduction(
            AppEnvironment::Production,
            $input = $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $input->expects($this->once())->method('hasOption')->willReturn(true);
        $input->expects($this->once())->method('getOption')->willReturn(true);

        $this->assertTrue($confirmation->confirmToProceed());
    }


    public function testProductionEnvShouldBeAskAboutConfirmationAndConfirmed(): void
    {
        $confirmation = new ApplicationInProduction(
            AppEnvironment::Production,
            $input = $this->createMock(InputInterface::class),
            $output = $this->createMock(SymfonyStyle::class)
        );

        $input->expects($this->once())->method('hasOption')->willReturn(false);
        $output
            ->expects($this->once())
            ->method('confirm')
            ->with('Do you really wish to run command?', false)
            ->willReturn(true);

        $this->assertTrue($confirmation->confirmToProceed());
    }

    public function testProductionEnvShouldBeAskAboutConfirmationAndNotConfirmed(): void
    {
        $confirmation = new ApplicationInProduction(
            AppEnvironment::Production,
            $input = $this->createMock(InputInterface::class),
            $output = $this->createMock(SymfonyStyle::class)
        );

        $input->expects($this->once())->method('hasOption')->willReturn(false);
        $output
            ->expects($this->once())
            ->method('confirm')
            ->with('Do you really wish to run command?', false)
            ->willReturn(false);

        $this->assertFalse($confirmation->confirmToProceed());
    }

    public static function notProductionEnvs(): \Traversable
    {
        yield 'Local' => [AppEnvironment::Local];
        yield 'Testing' => [AppEnvironment::Testing];
        yield 'Stage' => [AppEnvironment::Stage];
    }
}
