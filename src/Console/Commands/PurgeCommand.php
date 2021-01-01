<?php

namespace Nomadnt\LumenPassport\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

use Nomadnt\LumenPassport\Passport;

class PurgeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'passport:purge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired refresh tokens and their associated tokens from the database';

    /**
     * Create a new passport purge command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $date = Carbon::now();

        $count = DB::table('oauth_refresh_tokens')->where('expires_at', '<', $date)->delete();

        if(Passport::$refreshTokensExpireAt AND Passport::$tokensExpireAt){
            $date->subDays(Passport::$refreshTokensExpireAt->diffinDays(Passport::$tokensExpireAt));
            // We assume it's safe to delete tokens that cannot be refreshed anyway
            $count += DB::table('oauth_access_tokens')->where('expires_at', '<', $date)->delete();
        }

        $this->info("Successfully deleted expired tokens: {$count}");
    }
}
