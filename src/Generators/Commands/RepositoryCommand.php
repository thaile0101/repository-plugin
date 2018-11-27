<?php namespace Elidev\Repository\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Elidev\Repository\Generators\RepositoryInterfaceGenerator;
use Elidev\Repository\Generators\RepositoryEloquentGenerator;
use Elidev\Repository\Generators\MigrationGenerator;
use Elidev\Repository\Generators\ModelGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RepositoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Collection
     * @var collection
     */
    protected $generators = null;


    /**
     * RepositoryCommand
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        parent::__construct();
        $this->generators = $collection;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->generators->push(new MigrationGenerator([
            'name'   => 'create_' . snake_case(str_plural($this->argument('name'))) . '_table',
            'fields' => $this->option('fillable'),
        ]));

        $modelGenerator = new ModelGenerator([
            'name'     => $this->argument('name'),
            'fillable' => $this->option('fillable'),
        ]);
        $this->generators->push($modelGenerator);

        $this->generators->push(new RepositoryInterfaceGenerator([
            'name'  => $this->argument('name')
        ]));

        foreach ($this->generators as $generator) {
            $generator->execute();
        }

        $model = str_replace([
            "\\",
            '/'
        ], '\\', $modelGenerator->getRootNamespace() . '\\' . $modelGenerator->getName());

        try {
            (new RepositoryEloquentGenerator([
                'name'      => $this->argument('name'),
                'model' => $model
            ]))->execute();
            $this->info("Repository created successfully.");
        } catch (Exception $e) {
            $this->error('Repository already exists!');

            return false;
        }
    }

    /**
     * The array of command arguments.
     *
     * @return array
     */
    public function getArguments()
    {
        return [
            [
                'name',
                InputArgument::REQUIRED,
                'The name of class being generated.',
                null
            ],
        ];
    }

    /**
     * The array of command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            [
                'fillable',
                null,
                InputOption::VALUE_OPTIONAL,
                'The fillable attributes.',
                null
            ],
            [
                'rules',
                null,
                InputOption::VALUE_OPTIONAL,
                'The rules of validation attributes.',
                null
            ],
            [
                'validator',
                null,
                InputOption::VALUE_OPTIONAL,
                'Adds validator reference to the repository.',
                null
            ],
            [
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force the creation if file already exists.',
                null
            ]
        ];
    }
}