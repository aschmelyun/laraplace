<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;

class SeedPixels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pixels:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds the redis database with random pixel data.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userEmails = User::pluck('email')->toArray();

        for($x=0; $x <= 99; $x++) {
            for($y=0; $y <= 99; $y++) {
                Redis::set("{$x}:{$y}", rand(0, 7) . ':' . Arr::random($userEmails));
            }
        }

        return 0;
    }
}
