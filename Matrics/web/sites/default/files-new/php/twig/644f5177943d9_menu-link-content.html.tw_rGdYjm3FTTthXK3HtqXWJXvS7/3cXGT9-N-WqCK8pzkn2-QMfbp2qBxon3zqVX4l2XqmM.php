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

/* modules/contrib/menu_item_extras/templates/menu-link-content.html.twig */
class __TwigTemplate_6d9ed417e0b8c173fd87cb0eae075264f6ec32c4ea25cc7df3624d29a55f6b0d extends \Twig\Template
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
        // line 15
        $macros["menu"] = $this->macros["menu"] = $this;
        // line 16
        echo "
";
        // line 17
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(twig_call_macro($macros["menu"], "macro_build_menu_link_content", [($context["attributes"] ?? null), ($context["menu_link_content"] ?? null), ($context["show_item_link"] ?? null), ($context["content"] ?? null), ($context["elements"] ?? null)], 17, $context, $this->getSourceContext()));
        echo "

";
    }

    // line 19
    public function macro_build_menu_link_content($__attributes__ = null, $__menu_link_content__ = null, $__show_item_link__ = null, $__content__ = null, $__elements__ = null, ...$__varargs__)
    {
        $macros = $this->macros;
        $context = $this->env->mergeGlobals([
            "attributes" => $__attributes__,
            "menu_link_content" => $__menu_link_content__,
            "show_item_link" => $__show_item_link__,
            "content" => $__content__,
            "elements" => $__elements__,
            "varargs" => $__varargs__,
        ]);

        $blocks = [];

        ob_start();
        try {
            // line 20
            echo "  ";
            $context["menu_dropdown_classes"] = [0 => "menu-dropdown", 1 => ((twig_get_attribute($this->env, $this->source,             // line 22
($context["elements"] ?? null), "#menu_level", [], "array", true, true, true, 22)) ? (("menu-dropdown-" . $this->sandbox->ensureToStringAllowed((($__internal_compile_0 = ($context["elements"] ?? null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["#menu_level"] ?? null) : null), 22, $this->source))) : ("")), 2 => (((($__internal_compile_1 =             // line 23
($context["elements"] ?? null)) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1["#view_mode"] ?? null) : null)) ? (("menu-type-" . $this->sandbox->ensureToStringAllowed((($__internal_compile_2 = ($context["elements"] ?? null)) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2["#view_mode"] ?? null) : null), 23, $this->source))) : (""))];
            // line 25
            echo "
  <div";
            // line 26
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [0 => ($context["menu_dropdown_classes"] ?? null)], "method", false, false, true, 26), 26, $this->source), "html", null, true);
            echo ">
    ";
            // line 27
            if (($context["show_item_link"] ?? null)) {
                // line 28
                echo "      ";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->getLink($this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["menu_link_content"] ?? null), "getTitle", [], "method", false, false, true, 28), 28, $this->source), $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["menu_link_content"] ?? null), "getUrlObject", [], "method", false, false, true, 28), 28, $this->source)), "html", null, true);
                echo "
    ";
            }
            // line 30
            echo "    ";
            if (($context["content"] ?? null)) {
                // line 31
                echo "      ";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["content"] ?? null), 31, $this->source), "html", null, true);
                echo "
    ";
            }
            // line 33
            echo "  </div>
";

            return ('' === $tmp = ob_get_contents()) ? '' : new Markup($tmp, $this->env->getCharset());
        } finally {
            ob_end_clean();
        }
    }

    public function getTemplateName()
    {
        return "modules/contrib/menu_item_extras/templates/menu-link-content.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  97 => 33,  91 => 31,  88 => 30,  82 => 28,  80 => 27,  76 => 26,  73 => 25,  71 => 23,  70 => 22,  68 => 20,  51 => 19,  44 => 17,  41 => 16,  39 => 15,);
    }

    public function getSourceContext()
    {
        return new Source("{#
/**
 * @file
 * Default theme implementation to present a menu link entity entity.
 *
 * Available variables:
 * - menu_link_content: Menu link entity.
 * - show_item_link: Indicator for menu item link.
 *
 * @see template_preprocess_menu_link_content()
 *
 * @ingroup themeable
 */
#}
{% import _self as menu %}

{{ menu.build_menu_link_content(attributes, menu_link_content, show_item_link, content, elements) }}

{% macro build_menu_link_content(attributes, menu_link_content, show_item_link, content, elements) %}
  {% set menu_dropdown_classes = [
    'menu-dropdown',
    elements['#menu_level'] is defined ? 'menu-dropdown-' ~ elements['#menu_level'],
    elements['#view_mode'] ? 'menu-type-' ~ elements['#view_mode'],
  ] %}

  <div{{ attributes.addClass(menu_dropdown_classes) }}>
    {% if show_item_link %}
      {{ link(menu_link_content.getTitle(), menu_link_content.getUrlObject()) }}
    {% endif %}
    {% if content %}
      {{ content }}
    {% endif %}
  </div>
{% endmacro %}
", "modules/contrib/menu_item_extras/templates/menu-link-content.html.twig", "/home/matrics/public_html/yogita/web/modules/contrib/menu_item_extras/templates/menu-link-content.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("import" => 15, "macro" => 19, "set" => 20, "if" => 27);
        static $filters = array("escape" => 26);
        static $functions = array("link" => 28);

        try {
            $this->sandbox->checkSecurity(
                ['import', 'macro', 'set', 'if'],
                ['escape'],
                ['link']
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
