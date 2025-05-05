<?php
namespace App\Command;

use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class GenerateFromXmlCommand extends Command
{
    protected static $defaultName = 'app:generate-from-xml';
    private $fs;

    public function __construct()
    {
        parent::__construct();
        $this->fs = new Filesystem();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:generate-from-xml')
            ->setDescription('Génère entités, getters/setters, FormTypes et templates à partir de config/orm.xml');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $xmlFile = $this->getProjectDir() . '/config/orm.xml';
        if (!file_exists($xmlFile)) {
            $output->writeln('<error>Fichier config/orm.xml introuvable.</>');
            return Command::FAILURE;
        }

        $xml = new SimpleXMLElement(file_get_contents($xmlFile));
        foreach ($xml->classes->class as $classNode) {
            $this->generateEntity((string)$classNode['name'], $classNode, $output);
            $this->generateFormType((string)$classNode['name'], $classNode, $output);
            $this->generateTwigTemplate((string)$classNode['name'], $classNode, $output);
        }

        $output->writeln('<info>Génération terminée.</>');
        return Command::SUCCESS;
    }

    private function generateEntity(string $name, SimpleXMLElement $node, OutputInterface $out): void
    {
        $className = ucfirst($name);
        $path = $this->getProjectDir() . "/src/Entity/{$className}.php";
        $out->writeln("→ Génération entité {$className}");

        $lines = [
            "<?php",
            "namespace App\Entity;",
            "",
            "use Doctrine\ORM\Mapping as ORM;",
            "",
            "#[ORM\Entity]",
            "class {$className}",
            "{"
        ];

        // ID
        $idName = (string)$node->id['name'];
        $lines[] = "    #[ORM\Id]";
        $lines[] = "    #[ORM\GeneratedValue]";
        $lines[] = "    #[ORM\Column]";
        $lines[] = "    private ?int \${$idName} = null;";
        $lines[] = "";
        // Getter for ID
        $methodSuffix = str_replace('_', '', ucwords($idName, '_'));
        $lines[] = "    public function get{$methodSuffix}(): ?int";
        $lines[] = "    {";
        $lines[] = "        return \$this->{$idName};";
        $lines[] = "    }";
        $lines[] = "";

        // Fields + getters/setters
        foreach ($node->field as $f) {
            $fname = (string)$f['name'];
            $type  = (string)$f['type'];
            $size  = isset($f['size']) ? (int)$f['size'] : null;

            $doctrineType = match($type) {
                'varchar' => 'string',
                'float'   => 'float',
                'date'    => '\DateTimeInterface',
                default   => 'string',
            };
            $col = "    #[ORM\Column(type: '{$doctrineType}'" . ($size ? ", length: {$size}" : "") . ")]";
            $lines[] = $col;
            $phpType = ltrim($doctrineType, '\\');
            $lines[] = "    private ?{$phpType} \${$fname} = null;";
            $lines[] = "";

            // Getter
            $methodSuffix = str_replace('_', '', ucwords($fname, '_'));
            $lines[] = "    public function get{$methodSuffix}(): ?" . $phpType;
            $lines[] = "    {";
            $lines[] = "        return \$this->{$fname};";
            $lines[] = "    }";
            $lines[] = "";

            // Setter
            $typeHint = $phpType === 'DateTimeInterface' ? "\\DateTimeInterface " : "{$phpType} ";
            $lines[] = "    public function set{$methodSuffix}({$typeHint}\${$fname}): self";
            $lines[] = "    {";
            $lines[] = "        \$this->{$fname} = \${$fname};";
            $lines[] = "        return \$this;";
            $lines[] = "    }";
            $lines[] = "";
        }

        $lines[] = "}";
        $this->fs->dumpFile($path, implode("\n", $lines));
    }

    private function generateFormType(string $name, SimpleXMLElement $node, OutputInterface $out): void
    {
        $className = ucfirst($name);
        $path = $this->getProjectDir() . "/src/Form/{$className}Type.php";
        $out->writeln("→ Génération FormType {$className}Type");

        $lines = [
            "<?php",
            "namespace App\Form;",
            "",
            "use App\Entity\\{$className};",
            "use Symfony\Component\Form\AbstractType;",
            "use Symfony\Component\Form\FormBuilderInterface;",
            "use Symfony\Component\OptionsResolver\OptionsResolver;",
            "use Symfony\Component\Form\Extension\Core\Type\TextType;",
            "use Symfony\Component\Form\Extension\Core\Type\DateType;",
            "use Symfony\Component\Form\Extension\Core\Type\NumberType;",
            "",
            "class {$className}Type extends AbstractType",
            "{",
            "    public function buildForm(FormBuilderInterface \$builder, array \$options): void",
            "    {"
        ];

        foreach ($node->field as $f) {
            $fname = (string)$f['name'];
            $type  = (string)$f['type'];
            $formType = match($type) {
                'varchar' => 'TextType::class',
                'float'   => 'NumberType::class',
                'date'    => 'DateType::class',
                default   => 'TextType::class',
            };
            $lines[] = "        \$builder->add('{$fname}', {$formType});";
        }

        $lines = array_merge($lines, [
            "    }",
            "",
            "    public function configureOptions(OptionsResolver \$resolver): void",
            "    {",
            "        \$resolver->setDefaults([",
            "            'data_class' => {$className}::class,",
            "        ]);",
            "    }",
            "}"
        ]);

        $this->fs->dumpFile($path, implode("\n", $lines));
    }

    private function generateTwigTemplate(string $name, SimpleXMLElement $node, OutputInterface $out): void
    {
        $twigName = strtolower($name);
        $dir = $this->getProjectDir() . "/templates/{$twigName}";
        $this->fs->mkdir($dir);

        $out->writeln("→ Génération Twig template {$twigName}/new.html.twig");
        $template = <<<TWIG
{% extends 'base.html.twig' %}
{% block title %}Créer un {$name}{% endblock %}

{% block body %}
<h1>Nouvel(le) {$name}</h1>
{{ form_start(form) }}
  {{ form_widget(form) }}
  <button class="btn">Enregistrer</button>
{{ form_end(form) }}
{% endblock %}
TWIG;

        $this->fs->dumpFile("{$dir}/new.html.twig", $template);
    }

    private function getProjectDir(): string
    {
        return dirname(__DIR__, 2);
    }
}
