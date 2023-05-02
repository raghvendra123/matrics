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

/* modules/contrib/better_exposed_filters/templates/bef-links.html.twig */
class __TwigTemplate_871b0693d5e54d4667c38a217a9ad976842b9f9fc0c3396c3ae7787cb2a5521c extends \Twig\Template
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
        // line 2
        $context["classes"] = [0 => "bef-links", 1 => ((        // line 4
($context["is_nested"] ?? null)) ? ("bef-nested") : (""))];
        // line 7
        $context["is_nested"] = true;
        // line 8
        echo "<input type=\"hidden\" name=\"";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_0 = ($context["element"] ?? null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["#name"] ?? null) : null), 8, $this->source), "html", null, true);
        echo "\" value=\"";
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed((($__internal_compile_1 = ($context["element"] ?? null)) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1["#value"] ?? null) : null), 8, $this->source), "html", null, true);
        echo "\" />
<div";
        // line 9
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [0 => ($context["classes"] ?? null)], "method", false, false, true, 9), 9, $this->source), "html", null, true);
        echo ">
  ";
        // line 10
        $context["current_nesting_level"] = 0;
        // line 11
        echo "  ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["children"] ?? null));
        $context['loop'] = [
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        ];
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
            // line 12
            echo "    ";
            $context["item"] = twig_get_attribute($this->env, $this->source, ($context["element"] ?? null), $context["child"], [], "any", false, false, true, 12);
            // line 13
            echo "    ";
            if (twig_in_filter($context["child"], ($context["selected"] ?? null))) {
                // line 14
                echo "      ";
                $context["new_nesting_level"] = twig_get_attribute($this->env, $this->source, ($context["depth"] ?? null), $context["child"], [], "any", false, false, true, 14);
                // line 15
                echo "      ";
                $context["item"] = twig_array_merge($this->sandbox->ensureToStringAllowed(($context["item"] ?? null), 15, $this->source), ["#attributes" => ["class" => "bef-link-active"]]);
                // line 16
                echo "    ";
            }
            // line 17
            echo "    ";
            $context["new_nesting_level"] = twig_get_attribute($this->env, $this->source, ($context["depth"] ?? null), $context["child"], [], "any", false, false, true, 17);
            // line 18
            echo "    ";
            $this->loadTemplate("@better_exposed_filters/bef-nested-elements.html.twig", "modules/contrib/better_exposed_filters/templates/bef-links.html.twig", 18)->display($context);
            // line 19
            echo "    ";
            $context["current_nesting_level"] = ($context["new_nesting_level"] ?? null);
            // line 20
            echo "  ";
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['child'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 21
        echo "</div>
";
    }

    public function getTemplateName()
    {
        return "modules/contrib/better_exposed_filters/templates/bef-links.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  113 => 21,  99 => 20,  96 => 19,  93 => 18,  90 => 17,  87 => 16,  84 => 15,  81 => 14,  78 => 13,  75 => 12,  57 => 11,  55 => 10,  51 => 9,  44 => 8,  42 => 7,  40 => 4,  39 => 2,);
    }

    public function getSourceContext()
    {
        return new Source("{%
  set classes = [
    'bef-links',
    is_nested ? 'bef-nested'
  ]
%}
{% set is_nested = true %}
<input type=\"hidden\" name=\"{{ element[\"#name\"] }}\" value=\"{{ element[\"#value\"] }}\" />
<div{{ attributes.addClass(classes) }}>
  {% set current_nesting_level = 0 %}
  {% for child in children %}
    {% set item = attribute(element, child) %}
    {% if child in selected %}
      {% set new_nesting_level = attribute(depth, child) %}
      {% set item = item|merge({'#attributes': {'class': 'bef-link-active'}}) %}
    {% endif %}
    {% set new_nesting_level = attribute(depth, child) %}
    {% include '@better_exposed_filters/bef-nested-elements.html.twig' %}
    {% set current_nesting_level = new_nesting_level %}
  {% endfor %}
</div>
", "modules/contrib/better_exposed_filters/templates/bef-links.html.twig", "/home/matrics/public_html/yogita/web/modules/contrib/better_exposed_filters/templates/bef-links.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("set" => 2, "for" => 11, "if" => 13, "include" => 18);
        static $filters = array("escape" => 8, "merge" => 15);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['set', 'for', 'if', 'include'],
                ['escape', 'merge'],
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
