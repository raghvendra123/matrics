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

/* modules/contrib/permissions_by_term/src/View/node-details.html.twig */
class __TwigTemplate_f6977efdbb736e96fd523941d7d298669f42a55558d0aaa245bcd2b03176e66c extends \Twig\Template
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
        echo t("This widget shows information about taxonomy term related permissions. It's being updated, as soon you make any related changes in the form.", array());
        echo "<br /><br />

<b>";
        // line 3
        echo t("Allowed users:", array());
        echo "</b>
";
        // line 4
        if ( !twig_test_empty(($context["users"] ?? null))) {
            // line 5
            echo "  ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["users"] ?? null));
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
            foreach ($context['_seq'] as $context["_key"] => $context["user"]) {
                // line 6
                echo "    ";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["user"], "displayname", [], "any", false, false, true, 6), 6, $this->source), "html", null, true);
                if ( !twig_get_attribute($this->env, $this->source, $context["loop"], "last", [], "any", false, false, true, 6)) {
                    echo ", ";
                }
                // line 7
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
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['user'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
        } else {
            // line 9
            echo "  <i>";
            echo t("No user restrictions.", array());
            echo "</i>
";
        }
        // line 11
        echo "<br />
<b>";
        // line 12
        echo t("Allowed roles:", array());
        echo "</b>
";
        // line 13
        if ( !twig_test_empty(($context["roles"] ?? null))) {
            // line 14
            echo "  ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["roles"] ?? null));
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
            foreach ($context['_seq'] as $context["_key"] => $context["role"]) {
                // line 15
                echo "    ";
                echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["role"], "label", [], "any", false, false, true, 15), 15, $this->source), "html", null, true);
                if ( !twig_get_attribute($this->env, $this->source, $context["loop"], "last", [], "any", false, false, true, 15)) {
                    echo ", ";
                }
                // line 16
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
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['role'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
        } else {
            // line 18
            echo "  <i>";
            echo t("No role restrictions.", array());
            echo "</i>
";
        }
    }

    public function getTemplateName()
    {
        return "modules/contrib/permissions_by_term/src/View/node-details.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  143 => 18,  128 => 16,  122 => 15,  104 => 14,  102 => 13,  98 => 12,  95 => 11,  89 => 9,  74 => 7,  68 => 6,  50 => 5,  48 => 4,  44 => 3,  39 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{% trans %}This widget shows information about taxonomy term related permissions. It's being updated, as soon you make any related changes in the form.{% endtrans %}<br /><br />

<b>{% trans %}Allowed users:{% endtrans %}</b>
{% if users is not empty %}
  {% for user in users  %}
    {{ user.displayname }}{% if not loop.last %}, {% endif %}
  {% endfor %}
{% else %}
  <i>{% trans %}No user restrictions.{% endtrans %}</i>
{% endif %}
<br />
<b>{% trans %}Allowed roles:{% endtrans %}</b>
{% if roles is not empty %}
  {% for role in roles %}
    {{ role.label }}{% if not loop.last %}, {% endif %}
  {% endfor %}
{% else %}
  <i>{% trans %}No role restrictions.{% endtrans %}</i>
{% endif %}", "modules/contrib/permissions_by_term/src/View/node-details.html.twig", "/home/matrics/public_html/yogita/web/modules/contrib/permissions_by_term/src/View/node-details.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("trans" => 1, "if" => 4, "for" => 5);
        static $filters = array("escape" => 6);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['trans', 'if', 'for'],
                ['escape'],
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
