<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* modules/custom/matrics_reports/templates/charts_score.html.twig */
class __TwigTemplate_5c06ac08b2653d67d75f1d3cecb89550a53545309abea029a6bc9cfbcdb14e0d extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        list($context["library"], $context["height"], $context["width"], $context["height_units"], $context["width_units"]) =         [((("charts_" . $this->sandbox->ensureToStringAllowed(($context["library"] ?? null), 1, $this->source)) . "/") . $this->sandbox->ensureToStringAllowed(($context["library"] ?? null), 1, $this->source)), twig_get_attribute($this->env, $this->source, ($context["options"] ?? null), "height", [], "any", false, false, true, 1), twig_get_attribute($this->env, $this->source, ($context["options"] ?? null), "width", [], "any", false, false, true, 1), twig_get_attribute($this->env, $this->source, ($context["options"] ?? null), "height_units", [], "any", false, false, true, 1), twig_get_attribute($this->env, $this->source, ($context["options"] ?? null), "width_units", [], "any", false, false, true, 1)];
        // line 2
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary($this->sandbox->ensureToStringAllowed(($context["library"] ?? null), 2, $this->source)), "html", null, true);
        echo "
<div ";
        // line 3
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["attributes"] ?? null), 3, $this->source), "html", null, true);
        echo " ";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["content_attributes"] ?? null), 3, $this->source), "html", null, true);
        echo "
        style=\"";
        // line 4
        if ( !twig_test_empty(($context["width"] ?? null))) {
            echo "width:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["width"] ?? null), 4, $this->source), "html", null, true);
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["width_units"] ?? null), 4, $this->source), "html", null, true);
            echo ";";
        }
        if ( !twig_test_empty(($context["height"] ?? null))) {
            echo "height:";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["height"] ?? null), 4, $this->source), "html", null, true);
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["height_units"] ?? null), 4, $this->source), "html", null, true);
            echo ";";
        }
        echo "\"></div>
";
    }

    public function getTemplateName()
    {
        return "modules/custom/matrics_reports/templates/charts_score.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  51 => 4,  45 => 3,  41 => 2,  39 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% set library, height, width, height_units, width_units = 'charts_' ~ library ~ '/' ~ library, options.height, options.width, options.height_units, options.width_units %}
{{ attach_library(\"#{ library }\") }}
<div {{ attributes }} {{ content_attributes }}
        style=\"{% if width is not empty %}width:{{ width }}{{ width_units }};{% endif %}{% if height is not empty %}height:{{ height }}{{ height_units }};{% endif %}\"></div>
", "modules/custom/matrics_reports/templates/charts_score.html.twig", "/home/matrics/public_html/yogita/web/modules/custom/matrics_reports/templates/charts_score.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("set" => 1, "if" => 4);
        static $filters = array("escape" => 2);
        static $functions = array("attach_library" => 2);

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['escape'],
                ['attach_library']
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
