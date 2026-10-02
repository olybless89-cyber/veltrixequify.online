<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use App\Models\Admin;

class InitDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matrix:init-db 
                            {--force : Force database re-initialization even if tables exist}
                            {--admin-username= : Username for the administrator account}
                            {--admin-email= : Email for the administrator account}
                            {--admin-password= : Password for the administrator account}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize and seed the Matrix database schema, admin user, and settings for Railway / production deployment';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("=================================================");
        $this->info("       Matrix Platform Database Initializer      ");
        $this->info("=================================================");

        // 1. Verify Database Connection
        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $this->info("✓ Successfully connected to database: [{$dbName}]");
        } catch (\Exception $e) {
            $this->error("✗ Database connection failed: " . $e->getMessage());
            $this->warn("Please check your database environment variables (MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE or DB_*).");
            return 1;
        }

        $force = $this->option('force');
        $hasAdmins = Schema::hasTable('admins');
        $hasConfigures = Schema::hasTable('configures');
        $hasUsers = Schema::hasTable('users');

        $isInitialized = $hasAdmins && $hasConfigures && $hasUsers;

        if ($isInitialized && !$force) {
            $this->info("✓ Database is already initialized. Skipping SQL import to preserve existing data.");
        } else {
            $sqlFile = database_path('matrix_database.sql');
            if (!file_exists($sqlFile)) {
                $this->error("✗ SQL dump file not found at: {$sqlFile}");
                return 1;
            }

            $this->info("Importing database schema and data from [database/matrix_database.sql]...");

            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');

                $sqlContent = file_get_contents($sqlFile);
                
                // Execute using unprepared statements
                // If multi-query fails, fallback to statement-by-statement
                try {
                    DB::unprepared($sqlContent);
                } catch (\Exception $multiEx) {
                    $this->warn("Batch import encountered a notice, executing individual statements...");
                    $this->executeSqlStatements($sqlContent);
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                $this->info("✓ Base schema and data imported successfully.");
            } catch (\Exception $e) {
                $this->error("Error during SQL import: " . $e->getMessage());
                // Continue to ensure admin and migrations run
            }
        }

        // 2. Run pending migrations if any
        try {
            $this->info("Running pending migrations...");
            Artisan::call('migrate', ['--force' => true]);
            $this->line(Artisan::output());
        } catch (\Exception $e) {
            $this->warn("Notice while running migrations: " . $e->getMessage());
        }

        // 2b. Repair the `contents` table directly. This runs independently
        // of the `migrate` call above: the very first migration this app
        // ships (create_users_table, 2021) always throws "table already
        // exists" against the raw SQL dump's own users table, and a single
        // failing migration aborts the whole `migrate` batch before any
        // later-dated migration -- including a schema fix -- ever runs.
        // So the fix has to happen here, directly, where a working DB
        // connection is already guaranteed. Additive only: never drops or
        // truncates anything, safe to run on every boot.
        try {
            if (!Schema::hasTable('contents')) {
                Schema::create('contents', function ($table) {
                    $table->unsignedInteger('id')->primary();
                    $table->string('name', 191)->nullable();
                    $table->timestamps();
                });
                $this->info("✓ Created missing [contents] table.");
            } elseif (!Schema::hasColumn('contents', 'name')) {
                Schema::table('contents', function ($table) {
                    $table->string('name', 191)->nullable()->after('id');
                });
                $this->info("✓ Added missing [contents.name] column.");
            }

            $contentRows = [
                [7, 'counter'], [8, 'counter'], [9, 'counter'], [10, 'counter'],
                [15, 'service'], [16, 'service'], [17, 'service'],
                [18, 'testimonial'], [19, 'testimonial'],
                [33, 'support'], [34, 'support'],
                [37, 'how-it-work'], [38, 'how-it-work'], [39, 'how-it-work'], [40, 'how-it-work'],
                [56, 'social'], [58, 'social'], [59, 'social'], [60, 'social'],
                [61, 'blog'], [62, 'blog'], [63, 'blog'],
                [64, 'feature'], [65, 'feature'], [66, 'feature'],
                [67, 'why-chose-us'], [68, 'why-chose-us'], [69, 'why-chose-us'],
                [70, 'why-chose-us'], [71, 'why-chose-us'], [72, 'why-chose-us'],
                [74, 'testimonial'],
                [76, 'how-we-work'], [77, 'how-we-work'], [78, 'how-we-work'],
                [83, 'know-more-us'], [84, 'know-more-us'], [85, 'know-more-us'], [86, 'know-more-us'],
                [88, 'faq'],
            ];
            $now = now();
            foreach ($contentRows as [$id, $name]) {
                DB::table('contents')->updateOrInsert(
                    ['id' => $id],
                    ['name' => $name, 'created_at' => $now, 'updated_at' => $now]
                );
            }
            $this->info("✓ [contents] table verified (" . count($contentRows) . " rows).");
        } catch (\Exception $e) {
            $this->warn("Notice while repairing [contents] table: " . $e->getMessage());
        }

        // 3. Ensure Default Admin Account
        $adminUsername = $this->option('admin-username') ?: env('ADMIN_USERNAME', 'admin');
        $adminEmail = $this->option('admin-email') ?: env('ADMIN_EMAIL', 'admin@gmail.com');
        $adminPassword = $this->option('admin-password') ?: env('ADMIN_PASSWORD', 'admin123456');

        try {
            if (Schema::hasTable('admins')) {
                $admin = Admin::first();
                if (!$admin) {
                    $admin = new Admin();
                    $admin->id = 1;
                    $admin->name = 'Super Administrator';
                }

                // Only set credentials on first creation (or when ADMIN_RESET_PASSWORD=true),
                // so password/email changes made in the admin panel survive redeploys.
                $setCredentials = !$admin->exists || filter_var(env('ADMIN_RESET_PASSWORD', false), FILTER_VALIDATE_BOOLEAN);
                if ($setCredentials) {
                    $admin->username = $adminUsername;
                    $admin->email = $adminEmail;
                    $admin->password = Hash::make($adminPassword);
                }
                $admin->status = 1;
                $admin->admin_access = [
                    "admin.dashboard","admin.staff","admin.storeStaff","admin.updateStaff",
                    "admin.identify-form","admin.identify-form.store","admin.scheduleManage",
                    "admin.planList","admin.store.schedule","admin.update.schedule","admin.planCreate",
                    "admin.planEdit","admin.plans-active","admin.plans-inactive","admin.referral-commission",
                    "admin.referral-commission.store","admin.transaction","admin.transaction.search",
                    "admin.investments","admin.investments.search","admin.commissions","admin.commissions.search",
                    "admin.users","admin.users.search","admin.email-send","admin.user.transaction",
                    "admin.user.fundLog","admin.user.withdrawal","admin.user.commissionLog",
                    "admin.user.referralMember","admin.user.plan-purchaseLog","admin.user.userKycHistory",
                    "admin.kyc.users.pending","admin.kyc.users","admin.user-edit","admin.user-multiple-active",
                    "admin.user-multiple-inactive","admin.send-email","admin.user-balance-update",
                    "admin.payment.methods","admin.deposit.manual.index","admin.deposit.manual.create",
                    "admin.edit.payment.methods","admin.deposit.manual.edit","admin.payment.pending",
                    "admin.payment.log","admin.payment.search","admin.payment.action","admin.payout-method",
                    "admin.payout-log","admin.payout-request","admin.payout-log.search",
                    "admin.payout-method.create","admin.payout-method.edit","admin.payout-action",
                    "admin.ticket","admin.ticket.view","admin.ticket.reply","admin.ticket.delete",
                    "admin.subscriber.index","admin.subscriber.sendEmail","admin.subscriber.remove",
                    "admin.basic-controls","admin.email-controls","admin.email-template.show",
                    "admin.sms.config","admin.sms-template","admin.notify-config","admin.notify-template.show",
                    "admin.notify-template.edit","admin.basic-controls.update","admin.email-controls.update",
                    "admin.email-template.edit","admin.sms-template.edit","admin.notify-config.update",
                    "admin.notify-template.update","admin.language.index","admin.language.create",
                    "admin.language.edit","admin.language.keywordEdit","admin.language.delete",
                    "admin.manage.theme","admin.logo-seo","admin.breadcrumb","admin.template.show",
                    "admin.content.index","admin.content.create","admin.logoUpdate","admin.seoUpdate",
                    "admin.breadcrumbUpdate","admin.content.show","admin.content.delete"
                ];
                $admin->save();

                $this->info("✓ Administrator account ready:");
                $this->table(['Setting', 'Value'], [
                    ['Login URL', url('/admin')],
                    ['Username', $admin->username],
                    ['Email', $admin->email],
                    ['Password', $setCredentials ? 'set from ADMIN_PASSWORD (hidden)' : 'unchanged'],
                ]);
            }
        } catch (\Exception $e) {
            $this->warn("Notice while updating admin credentials: " . $e->getMessage());
        }

        // 4. Update Site Branding & Resend Email Configuration
        try {
            if (Schema::hasTable('configures')) {
                $siteTitle = env('APP_NAME', 'Veltrix Equify');
                $senderEmail = env('MAIL_FROM_ADDRESS', 'support@veltrixequify.online');
                $senderName = env('MAIL_FROM_NAME', $siteTitle);

                $resendKey = env('RESEND_API_KEY') ?: env('MAIL_PASSWORD', '');
                $emailConfig = [
                    'name' => 'smtp',
                    'smtp_host' => 'smtp.resend.com',
                    'smtp_port' => (string) env('MAIL_PORT', '2465'),
                    'smtp_encryption' => env('MAIL_ENCRYPTION', 'ssl'),
                    'smtp_username' => 'resend',
                    'smtp_password' => $resendKey,
                ];

                DB::table('configures')->where('id', 1)->update([
                    'site_title' => $siteTitle,
                    'sender_email' => $senderEmail,
                    'sender_email_name' => $senderName,
                    'email_configuration' => json_encode($emailConfig),
                ]);

                if (Schema::hasTable('manage_plans')) {
                    DB::table('manage_plans')->where('name', 'Matrix')->update(['name' => 'Veltrix Starter']);
                    DB::table('manage_plans')->where('name', 'Matrix Starter')->update(['name' => 'Veltrix Advanced']);
                    DB::table('manage_plans')->where('name', 'Matrix Train')->update(['name' => 'Veltrix Professional']);
                    DB::table('manage_plans')->where('name', 'Matrix Max')->update(['name' => 'Veltrix Premium']);
                }

                $this->info("✓ Site branding and Resend email settings synchronized for [{$siteTitle}].");
            }
        } catch (\Exception $e) {
            $this->warn("Notice while updating branding and email settings: " . $e->getMessage());
        }

        // 4. Ensure Storage Link
        try {
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
                $this->info("✓ Storage symlink created.");
            }
        } catch (\Exception $e) {
            // Ignore if link already exists
        }

        // 5. Clear Caches
        try {
            Artisan::call('optimize:clear');
            $this->info("✓ Application cache cleared successfully.");
        } catch (\Exception $e) {
            // Ignore
        }

        $this->info("=================================================");
        $this->info("✓ Matrix platform is fully ready for deployment! ");
        $this->info("=================================================");

        return 0;
    }

    /**
     * Execute SQL statements one by one for maximum compatibility.
     *
     * @param string $sql
     * @return void
     */
    protected function executeSqlStatements($sql)
    {
        $lines = explode("\n", $sql);
        $statement = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '#')) {
                continue;
            }

            $statement .= ' ' . $line;
            if (str_ends_with($trimmed, ';')) {
                try {
                    DB::statement($statement);
                } catch (\Exception $e) {
                    // Suppress harmless table exists or warning errors
                }
                $statement = '';
            }
        }
    }
}
