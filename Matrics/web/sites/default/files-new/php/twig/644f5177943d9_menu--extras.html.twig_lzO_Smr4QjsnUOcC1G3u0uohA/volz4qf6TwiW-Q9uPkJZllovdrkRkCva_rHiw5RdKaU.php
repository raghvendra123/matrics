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

/* themes/custom/matrics/templates/menu/menu--extras.html.twig */
class __TwigTemplate_73085b7f6fd803f596e4a10307929c123ebb34d1f3f6da4226b0a3ffed94feca extends \Twig\Template
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
        // line 22
        echo "
";
        // line 23
        $macros["menu"] = $this->macros["menu"] = $this->loadTemplate("menu-levels.html.twig", "themes/custom/matrics/templates/menu/menu--extras.html.twig", 23)->unwrap();
        // line 24
        echo "
";
        // line 25
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(twig_call_macro($macros["menu"], "macro_menu_links", [($context["items"] ?? null), ($context["attributes"] ?? null), 0], 25, $context, $this->getSourceContext()));
        echo "
";
    }

    public function getTemplateName()
    {
        return "themes/custom/matrics/templates/menu/menu--extras.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  47 => 25,  44 => 24,  42 => 23,  39 => 22,);
    }

    public function getSourceContext()
    {
        return new Source("{#
/**
 * @file
 * Theme override to display a menu.
 *
 * Available variables:
 * - menu_name: The machine name of the menu.
 * - items: A nested list of menu items. Each menu item contains:
 *   - attributes: HTML attributes for the menu item.
 *   - content.children: The menu item child items.
 *   - title: The menu link title.
 *   - url: The menu link url, instance of \\Drupal\\Core\\Url
 *   - content: The field item content.
 *   - localized_options: Menu link localized options.
 *   - is_expanded: TRUE if the link has visible children within the current
 *     menu tree.
 *   - is_collapsed: TRUE if the link has children within the current menu tree
 *     that are not currently visible.
 *   - in_active_trail: TRUE if the link is in the active trail.
 */
#}

{% import \"menu-levels.html.twig\" as menu %}

{{ menu.menu_links(items, attributes, 0) }}
", "themes/custom/matrics/templates/menu/menu--extras.html.twig", "/home/matrics/public_html/yogita/web/themes/custom/matrics/templates/menu/menu--extras.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("import" => 23);
        static $filters = array();
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['import'],
                [],
                []
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
