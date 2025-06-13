<?php

namespace Green\Auth\Console\Commands;

use Green\Auth\Console\Commands\Concerns\CollectsConfiguration;
use Green\Auth\Console\Commands\Concerns\DisplaysConfiguration;
use Green\Auth\Console\Commands\Concerns\GeneratesFiles;
use Green\Auth\Console\Commands\Concerns\GeneratesModels;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    use CollectsConfiguration;
    use DisplaysConfiguration;
    use GeneratesFiles;
    use GeneratesModels;

    /**
     * コンソールコマンドの名前とシグネチャ
     */
    protected $signature = 'green-auth:install
                            {--force : Overwrite existing files}
                            {--skip-models : Skip model generation}
                            {--skip-migrations : Skip migration generation}
                            {--skip-resources : Skip Filament resource generation}';

    /**
     * コンソールコマンドの説明
     */
    protected $description = 'Install Green Auth package with interactive setup';

    /**
     * セットアップ設定
     */
    protected array $config = [];

    /**
     * コンソールコマンドの実行
     *
     * @return int 実行結果コード
     */
    public function handle(): int
    {
        $this->info(__('green-auth::install.title'));
        $this->newLine();

        $this->collectBasicConfiguration();
        $this->collectModelConfiguration();
        $this->collectAuthConfiguration();
        $this->collectDatabaseConfiguration();
        $this->collectFilamentConfiguration();

        if (!$this->confirmConfiguration()) {
            $this->warn(__('green-auth::install.messages.installation_cancelled'));
            return self::FAILURE;
        }

        $this->generateFiles();

        $this->newLine();
        $this->info(__('green-auth::install.messages.installation_complete'));
        $this->displayNextSteps();

        return self::SUCCESS;
    }
}
