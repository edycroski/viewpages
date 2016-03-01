<?php

use Illuminate\Database\Seeder;
use Delatbabel\ViewPages\Models\Vpage;

class ExamplePageSeeder extends Seeder
{
    /**
     * Override this function to provide a base path
     *
     * @return string
     */
    protected function getBasePath()
    {
        return base_path('database/seeds/examples');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('vpages')->delete();

        // Sample page directory
        $topdir = $this->getBasePath();

        foreach (scandir($topdir) as $dirname) {
            // Read all of the files and directories under the top level directory.
            if (($dirname == '.') || ($dirname == '..')) {
                continue;
            }

            // If it's a file, load it directly.
            if (! is_dir($topdir . DIRECTORY_SEPARATOR . $dirname)) {
                if (strpos('.blade.php', $dirname)) {
                    $page_name = str_replace('.blade.php', '', $dirname);
                    $pagetype = 'blade';
                } elseif (strpos('.twig', $dirname)) {
                    $page_name = str_replace('.twig', '', $dirname);
                    $pagetype = 'twig';
                } else {
                    continue;
                }

                // Create the page
                Vpage::create([
                    'pagekey'           => $page_name,
                    'url'               => $page_name,
                    'name'              => $page_name,
                    'pagetype'          => $pagetype,
                    'description'       => $page_name . ' page loaded from ' . $dirname,
                    'content'           => file_get_contents($topdir . DIRECTORY_SEPARATOR .
                        $dirname),
                ]);
                continue;
            }

            // Read all of the files in each directory.
            foreach (scandir($topdir . DIRECTORY_SEPARATOR . $dirname) as $filename) {
                if (($filename == '.') || ($filename == '..')) {
                    continue;
                }

                if (strpos('.blade.php', $filename)) {
                    $page_name = str_replace('.blade.php', '', $filename);
                    $pagetype = 'blade';
                } elseif (strpos('.twig', $filename)) {
                    $page_name = str_replace('.twig', '', $filename);
                    $pagetype = 'twig';
                } else {
                    continue;
                }

                // Create the page
                Vpage::create([
                    'pagekey'           => $dirname . '.' . $page_name,
                    'url'               => $dirname . '/' . $page_name,
                    'name'              => $dirname . '.' . $page_name,
                    'pagetype'          => $pagetype,
                    'description'       => $page_name . ' page loaded from ' . $dirname . '/' . $filename,
                    'content'           => file_get_contents($topdir . DIRECTORY_SEPARATOR .
                        $dirname . DIRECTORY_SEPARATOR . $filename),
                ]);
            }
        }
    }
}
