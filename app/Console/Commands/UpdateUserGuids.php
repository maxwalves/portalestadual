<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class UpdateUserGuids extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-guids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update missing GUIDs for existing users by matching against LDAP';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting GUID update for users...');

        // Get all users without GUIDs
        $users = User::whereNull('guid')->orWhere('guid', '')->get();

        $this->info("Found {$users->count()} users without GUIDs.");

        $updated = 0;
        $failed  = 0;

        foreach ($users as $user) {
            $this->output->write("Processing {$user->username}... ");

            try {
                // Try to find the user in LDAP
                $ldapUser = null;

                // Try by username (samaccountname)
                if (! empty($user->username)) {
                    $ldapUser = LdapUser::where('samaccountname', '=', $user->username)->first();
                }

                // If not found, try by email
                if (! $ldapUser && ! empty($user->email)) {
                    $emailWithoutDomain = strstr($user->email, '@', true);
                    if ($emailWithoutDomain) {
                        $ldapUser = LdapUser::where('samaccountname', '=', $emailWithoutDomain)->first();
                    }

                    if (! $ldapUser) {
                        $ldapUser = LdapUser::where('mail', '=', $user->email)->first();
                    }
                }

                if ($ldapUser) {$objectGuid = $ldapUser->getFirstAttribute('objectguid');

                    if ($objectGuid) {
                        $user->guid   = $this->formatGuid($objectGuid);
                        $user->domain = 'default';
                        $user->save();

                        $this->line("<info>Updated!</info>");
                        $updated++;
                    } else {
                        $this->line("<comment>No GUID found in LDAP.</comment>");
                        $failed++;
                    }} else {
                    $this->line("<comment>Not found in LDAP.</comment>");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->line("<error>Error: {$e->getMessage()}</error>");
                Log::error("Error updating GUID for user {$user->username}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("GUID update completed. Updated: {$updated}, Failed: {$failed}");

        if ($updated > 0) {
            $this->info("The application should now properly identify these users during login.");
        }
        return Command::SUCCESS;
    }

    /**
     * Formata o GUID do LDAP para um formato seguro no banco de dados
     *
     * @param string $guid O GUID binário do LDAP
     * @return string O GUID formatado em UUID string
     */
    private function formatGuid($guid)
    {
        if (empty($guid)) {
            return null;
        }

        // Converte o GUID binário para string hexadecimal
        $hex_guid = bin2hex($guid);

        // Formata seguindo o padrão UUID
        $formatted = sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex_guid, 6, 2) . substr($hex_guid, 4, 2) . substr($hex_guid, 2, 2) . substr($hex_guid, 0, 2),
            substr($hex_guid, 10, 2) . substr($hex_guid, 8, 2),
            substr($hex_guid, 14, 2) . substr($hex_guid, 12, 2),
            substr($hex_guid, 16, 4),
            substr($hex_guid, 20, 12)
        );

        return $formatted;
    }
}
