<?php

namespace Laravel\Spark\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spark:install {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Spark scaffolding into the application';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->installNpmPackageConfig();
        $this->installGulpFile();
        $this->installServiceProviders();
        $this->installMiddleware();
        $this->installRoutes();
        $this->installModels();
        $this->installMigrations();
        $this->installViews();
        $this->updateAuthConfig();
        $this->installJavaScript();
        $this->installSass();
        $this->installEnvironmentVariables();
        $this->installTerms();

        $this->table(
            ['Task', 'Status'],
            [
                ['Installing Spark Features', '<info>✔</info>'],
            ]
        );

        if ($this->option('force') || $this->confirm('Would you like to run your database migrations?', 'yes')) {
            (new Process('php artisan migrate', base_path()))->setTimeout(null)->run();
        }

        if ($this->option('force') || $this->confirm('Would you like to install your NPM dependencies?', 'yes')) {
            (new Process('npm install', base_path()))->setTimeout(null)->run();
        }

        if ($this->option('force') || $this->confirm('Would you like to run Gulp?', 'yes')) {
            (new Process('gulp', base_path()))->setTimeout(null)->run();
        }

        $this->displayPostInstallationNotes();
    }

    /**
     * Install the "package.json" file for the project.
     *
     * @return void
     */
    protected function installNpmPackageConfig()
    {
        copy(
            SPARK_PATH.'/resources/stubs/package.json',
            base_path('package.json')
        );
    }

    /**
     * Install the "gulpfile.json" file for the project.
     *
     * @return void
     */
    protected function installGulpFile()
    {
        copy(
            SPARK_PATH.'/resources/stubs/gulpfile.js',
            base_path('gulpfile.js')
        );
    }

    /**
     * Generate and install the application Spark service provider.
     *
     * @return void
     */
    protected function installServiceProviders()
    {
        copy(
            SPARK_PATH.'/resources/stubs/app/Providers/SparkServiceProvider.php',
            app_path('Providers/SparkServiceProvider.php')
        );

        $this->setNamespace(app_path('Providers/SparkServiceProvider.php'));
    }

    /**
     * Install the customized Spark middleware.
     *
     * @return void
     */
    protected function installMiddleware()
    {
        copy(
            SPARK_PATH.'/resources/stubs/app/Http/Middleware/Authenticate.php',
            app_path('Http/Middleware/Authenticate.php')
        );

        $this->setNamespace(app_path('Http/Middleware/Authenticate.php'));

        if (! file_exists(app_path('Http/Middleware/VerifyCsrfToken.php'))) {
            copy(
                SPARK_PATH.'/resources/stubs/app/Http/Middleware/VerifyCsrfToken.php',
                app_path('Http/Middleware/VerifyCsrfToken.php')
            );

            $this->setNamespace(app_path('Http/Middleware/VerifyCsrfToken.php'));
        } else {
            $originalFile = file_get_contents(app_path('Http/Middleware/VerifyCsrfToken.php'));
            $newFile = file_get_contents(SPARK_PATH.'/resources/stubs/app/Http/Middleware/VerifyCsrfToken.php');
            $updatedMiddleware = $this->updateHidden($newFile, $originalFile);

            file_put_contents(app_path('Http/Middleware/VerifyCsrfToken.php'), $updatedMiddleware);
        }
    }

    /**
     * Install the routes for the application.
     *
     * @return void
     */
    protected function installRoutes()
    {
        copy(
            app_path('Http/routes.php'),
            app_path('Http/routes-backup-' . date('Y_m_d_His') . '.php')
        );

        copy(
            SPARK_PATH.'/resources/stubs/app/Http/routes.php',
            app_path('Http/routes.php')
        );
    }

    /**
     * Install the customized Spark models.
     *
     * @return void
     */
    protected function installModels()
    {
        $originalUser = file_get_contents(app_path('User.php'));
        $newUser = file_get_contents(SPARK_PATH.'/resources/stubs/app/User.php');

        $updatedUser = $this->updateHidden($newUser, $originalUser);
        $updatedUser = $this->updateDates($newUser, $updatedUser);
        $updatedUser = $this->updateAppends($newUser, $updatedUser);
        $updatedUser = $this->updateFillable($newUser, $updatedUser);
        $updatedUser = $this->updateImplements($newUser, $updatedUser);
        $updatedUser = $this->updateUseIncludes($newUser, $updatedUser);
        $updatedUser = $this->updateUseStatements($newUser, $updatedUser);

        file_put_contents(app_path('User.php'), $updatedUser);

        copy(
            SPARK_PATH.'/resources/stubs/app/Team.php',
            app_path('Team.php')
        );

        $this->setNamespace(app_path('Team.php'));
    }

    /**
     * Install the user migration file.
     *
     * @return void
     */
    protected function installMigrations()
    {
        copy(
            SPARK_PATH.'/resources/stubs/database/migrations/2014_10_12_000000_create_or_update_users_table_for_spark.php',
            database_path('migrations/2014_10_12_000000_create_or_update_users_table_for_spark.php')
        );

        usleep(1000);

        copy(
            SPARK_PATH.'/resources/stubs/database/migrations/2014_10_12_200000_create_teams_tables_for_spark.php',
            database_path('migrations/2014_10_12_200000_create_teams_tables_for_spark.php')
        );
    }

    /**
     * Install the default views for the application.
     *
     * @return void
     */
    protected function installViews()
    {
        copy(
            SPARK_PATH.'/resources/views/home.blade.php',
            base_path('resources/views/home.blade.php')
        );
    }

    /**
     * Update the "auth" configuration file.
     *
     * @return void
     */
    protected function updateAuthConfig()
    {
        $path = config_path('auth.php');

        file_put_contents($path, str_replace(
            'emails.password', 'spark::emails.auth.password.email', file_get_contents($path)
        ));
    }

    /**
     * Install the default JavaScript file for the application.
     *
     * @return void
     */
    protected function installJavaScript()
    {
        if (! is_dir('resources/assets/js')) {
            mkdir(base_path('resources/assets/js'));
        }

        if (! is_dir('resources/assets/js/spark')) {
            mkdir(base_path('resources/assets/js/spark'));
        }

        copy(
            SPARK_PATH.'/resources/stubs/resources/assets/js/app.js',
            base_path('resources/assets/js/app.js')
        );

        copy(
            SPARK_PATH.'/resources/stubs/resources/assets/js/spark/components.js',
            base_path('resources/assets/js/spark/components.js')
        );
    }

    /**
     * Install the default Sass file for the application.
     *
     * @return void
     */
    protected function installSass()
    {
        copy(
            SPARK_PATH.'/resources/stubs/resources/assets/sass/app.scss',
            base_path('resources/assets/sass/app.scss')
        );
    }

    /**
     * Install the environment variables for the application.
     *
     * @return void
     */
    protected function installEnvironmentVariables()
    {
        $env = file_get_contents(base_path('.env'));

        if (str_contains($env, 'AUTHY_KEY=')) {
            return;
        }

        (new Filesystem)->append(
            base_path('.env'),
            PHP_EOL.'AUTHY_KEY='.PHP_EOL.PHP_EOL.
            'STRIPE_KEY='.PHP_EOL.
            'STRIPE_SECRET='.PHP_EOL
        );
    }

    /**
     * Install the "Terms Of Service" Markdown file.
     *
     * @return void
     */
    protected function installTerms()
    {
        file_put_contents(
            base_path('terms.md'), 'This page is generated from the `terms.md` file in your project root.'
        );
    }

    /**
     * Display the post-installation information to the user.
     *
     * @return void
     */
    protected function displayPostInstallationNotes()
    {
        $this->comment('Post Installation Notes:');

        $this->line(PHP_EOL.'     → Set <info>AUTHY_KEY</info>, <info>STRIPE_KEY</info>, & <info>STRIPE_SECRET</info> Environment Variables');
    }

    /**
     * Set the correct namespace for a given file.
     *
     * @return bool
     */
    protected function setNamespace($filePath)
    {
        $userFile = file_get_contents(app_path('User.php'));
        $newFile = file_get_contents($filePath);
        $matches = [];

        preg_match('/namespace (.*?)\;/ms', $userFile, $matches);

        if (count($matches) < 2) {
            return false;
        }

        $namespace = trim($matches[1]);
        $newFile = str_replace(" App\\", " {$namespace}\\", $newFile);
        $newFile = str_replace(" App;", " {$namespace};", $newFile);
        file_put_contents($filePath, $newFile);

        return true;
    }


protected function updateExcept($newFile, $originalFile) {
    return $this->updateFile('/protected \$except \= \[(.*?)\]\;/sm',
        '/(.*protected \$except \= \[).*?(\]\;.*)/ms',
        '/(class VerifyCsrfToken .*?\{\n)/ms',
        $newFile,
        $originalFile,
        "\n        ",
        ",\n    ",
        "    /**\n     * The URIs that should be excluded from CSRF verification.\n     *\n     * @var array\n     */\n    protected \$except = [\n        ",
        "\n    ];\n"
    );
}


    protected function updateHidden($newFile, $originalFile) {
        return $this->updateFile('/protected \$appends \= \[(.*?)\]\;/sm',
            '/(.*protected \$appends \= \[).*?(\]\;.*)/ms',
            '/(class User extends.*use.*?\;\n)/ms',
            $newFile,
            $originalFile,
            "\n        ",
            ",\n    ",
            "    protected \$hidden = [\n        ",
            "\n    ];\n"
        );
    }

    protected function updateDates($newFile, $originalFile)
    {
        return $this->updateFile('/protected \$dates \= \[(.*?)\]\;/sm',
            '/(.*protected \$dates \= \[).*?(\]\;.*)/ms',
            '/(class User extends.*use.*?\;\n)/ms',
            $newFile,
            $originalFile,
            "\n        ",
            ",\n    ",
            "    protected \$dates = [\n        ",
            "\n    ];\n",
            ",\n        "
        );
    }

    protected function updateAppends($newFile, $originalFile) {
        return $this->updateFile('/protected \$appends \= \[(.*?)\]\;/sm',
            '/(.*protected \$appends \= \[).*?(\]\;.*)/ms',
            '/(class User extends.*use.*?\;\n)/ms',
            $newFile,
            $originalFile,
            "\n        ",
            ",\n    ",
            "    protected \$appends = [\n        ",
            "\n    ];\n",
            ",\n        "
        );
    }

    protected function updateFillable($newFile, $originalFile) {
        return $this->updateFile('/protected \$fillable \= \[(.*?)\]\;/sm',
            '/(.*protected \$fillable \= \[).*?(\]\;.*)/ms',
            '/(class User extends.*use.*?\;\n)/ms',
            $newFile,
            $originalFile,
            "\n        ",
            ",\n    ",
            "    protected \$fillable = [\n        ",
            "\n    ];\n",
            ",\n        "
        );
    }

    protected function updateImplements($newFile, $originalFile) {
        return $this->updateFile('/class User extends .*? implements (.*?)\{\n/sm',
            '/(class User extends .*? implements ).*?(\{\n)/ms',
            '/(class User extends .*?)\n/',
            $newFile,
            $originalFile,
            '',
            "\n",
            " implements ",
            "\n    ];\n",
            ",\n                                    ",
            true,
            ['AuthenticatableContract']
        );
    }

    protected function updateUseIncludes($newFile, $originalFile) {
        return $this->updateFile('/class User .*?use(.*?)\;/sm',
            '/(class User .*?use).*?(\;)/ms',
            '/(class User.*?\{)/ms',
            $newFile,
            $originalFile,
            " ",
            "",
            "use ",
            ";\n",
            ",\n        ",
            true,
            ['Authenticatable']
        );
    }

    protected function updateUseStatements($newFile, $originalFile) {
        return $this->updateFile('/\<\?php.*?namespace.*?\;(.*?)\nclass User/sm',
            '/(\<\?php.*?namespace.*?\;).*?(\nclass User)/ms',
            '',
            $newFile,
            $originalFile,
            "\n\n",
            ";\n",
            '',
            '',
            ";\n",
            true,
            [],
            ";"
        );
    }

    /**
     * @param $newUser
     * @param $matches
     * @param $originalUser
     *
     * @return string
     */
    protected function updateFile($detectionRegExp, $replacementRegExp, $insertionRegExp, $newFile, $originalFile,
        $replacementPrepend = '', $replacementAppend = '', $insertionPrepend = '', $insertionAppend = '', $replacementSeparator = ',',
        $sortReplacements = false, array $eliminateItems = [], $detectionSeparator = ',')
    {
        $matches = [];

        preg_match($detectionRegExp, $newFile, $matches);

        if (count($matches) === 2) {
            $newItems = array_map('trim', explode($detectionSeparator, $matches[1]));

            preg_match($detectionRegExp, $originalFile, $matches);

            if (! count($matches)) {
                if ($sortReplacements) {
                    $newItems = $this->sortArray($newItems);
                }

                $items = $insertionPrepend . implode($replacementSeparator, array_filter(array_unique($newItems))) . $insertionAppend;
                return preg_replace($insertionRegExp, "$1{$items}", $originalFile);
            }

            $originalItems = array_map('trim', explode($detectionSeparator, $matches[1]));
            $items = array_merge($originalItems, $newItems);
            $items = array_filter(array_unique($items));

            foreach ($eliminateItems as $eliminateItem) {
                if (($key = array_search($eliminateItem, $items)) !== false) {
                    unset($items[$key]);
                }
            }

            if ($sortReplacements) {
                $newItems = $this->sortArray($newItems);
            }

            $items = $replacementPrepend . implode($replacementSeparator, $items) . $replacementAppend;
            return preg_replace($replacementRegExp, "$1{$items}$2", $originalFile);
        }

        return $originalFile;
    }

    /**
     * @param $sortReplacements
     *
     * @return mixed
     */
    protected function sortArray(array $array)
    {
        natcasesort($array);

        return $array;
    }
}
