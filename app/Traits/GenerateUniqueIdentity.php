<?php

namespace App\Traits;


trait GenerateUniqueIdentity
{
    protected $random;
    protected $exist;
    protected $tested;
    protected $tableName;
    protected $columnName;
    protected $abbr;
    protected $unique;


    /**
     * Generate a Uniqe ID
     * @param string $tableName
     * @param string $abbr
     * @param string|NULL $columnName
     *
     * @return string
     */
    public static function generate(string $tableName, string $abbr, string $columnName = null)
    {
        return static::uniqueIdentity($tableName, $abbr, $columnName);
    }

    /**
     * Generate Unique ID in related to given table and column name
     *
     * @param string $tableName
     * @param string $abbr
     * @param string|NULL $columnName
     *
     * @return string
     */
    protected static function uniqueIdentity(string $tableName, string $abbr, string $columnName = null)
    {
        // instantiate $unique to null
        $unique = false;
        // Store tested results in array to not test them again
        $tested = [];

        do {
            // Generate random characters of 8
            $random = (string) $abbr . strtoupper(substr(md5(time()), 0, 8));

            // Check if it's already testing
            // If so, don't query the database again
            if (in_array($random, $tested)) {
                continue;
            }

            // Check if it is unique in the database
            $exist = \Illuminate\Support\Facades\DB::table($tableName)->where($columnName ?? 'unique_id', $random)->exists();

            // Store the random characters in the tested array
            // To keep track which ones are already tested
            $tested[] = $random;

            // String appears to be unique
            if ($exist === false) {
                // Set unique to true to break the loop
                $unique = true;
            }

            // If unique is still false at this point it will just repeat all the steps until
            // it has generated a random string of characters
        } while (!$unique);

        return $random;
    }

    /**
     * Generate Reference Number for Payments Table
     *
     * @param string $tableName|NULL
     * @param int $stringLength|13
     *
     * @return string
     */
    public static function generateReference(string $tableName = null, int $stringLength = 13)
    {
        return static::uniqueReference($tableName, $stringLength);
    }

    /**
     * Create reference number for payment
     *
     * @param string|NULL $tableName|NULL
     * @param int $stringLength|13
     *
     * @return string
     */
    protected static function uniqueReference(string $tableName = null, int $stringLength = 13)
    {
        // Store tested results in array to not test them again
        $tested = [];

        do {
            // Generate random characters of $stringLength or 13
            $random = \Illuminate\Support\Str::random($stringLength);
            // Check if it's already testing
            // If so, don't query the database again
            if (in_array($random, $tested)) {
                continue;
            }

            // Check if it is unique in the database
            $exist = \Illuminate\Support\Facades\DB::table($tableName ?? 'payments')->where('reference', $random)->exists();

            // Store the random characters in the tested array
            // To keep track which ones are already tested
            $tested[] = $random;

            // String appears to be unique
            if ($exist === false) {
                // Set unique to true to break the loop
                $unique = true;
            }

            // If unique is still false at this point it will just repeat all the steps until
            // it has generated a random string of characters
        } while (!$unique);

        return $random;
    }
}
