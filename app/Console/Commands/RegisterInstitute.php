<?php

namespace App\Console\Commands;

use App\Services\ParentHub\ParentHubService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RegisterInstitute extends Command
{
    protected $signature = 'parenthub:register';

    protected $description =
    'Register institute with Parent Hub';

    public function __construct(
        private ParentHubService $parentHubService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {

            $this->info(
                'Registering institute...'
            );

            $registered =
                $this->parentHubService
                ->registerInstitute();

            if (!$registered) {

                $this->error(
                    'Institute registration failed.'
                );

                return self::FAILURE;
            }

            $this->info(
                'Institute registered successfully.'
            );

            return self::SUCCESS;
        } catch (Throwable $e) {

            Log::error(
                'Parent Hub Registration Command Failed',
                [
                    'message' =>
                    $e->getMessage(),

                    'file' =>
                    $e->getFile(),

                    'line' =>
                    $e->getLine(),
                ]
            );

            $this->error(
                $e->getMessage()
            );

            return self::FAILURE;
        }
    }
}
