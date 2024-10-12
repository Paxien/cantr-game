<?php


/**
 * Responsible for purifing input html data to disallow XSS,
 * but allow both valid html and css (inline in <style> tags).
 *
 * This version of the CSSTidy library doesn't offer CSS selector validation.
 * In class NotePurifier I'm injecting custom implementation of a method discard_invalid_selectors()
 * (as a method of CSSTidyOptimiseValidationExtension which is subclass of csstidy_optimise)
 * to allow only alphanumeric and some special characters in selectors.
 * IT'S VERY IMPORTANT. It's the only reason why we are secure against XSS attacks.
 *
 * Unfortunately value of field $optimise set in csstidy constructor is then overwritten in method parse() which uses it for processing.
 * It's some kind of hack in original code to make it work with php4.
 * The only way to make it work without altering the original code was extending csstidy class
 * to make it intercept getting and setting $optimise property using __get and __set magic.
 *
 * It could be removed when:
 * The new version of CSSTidy gets correct CSS selector validation
 * Hack from original CSSTidy code gets removed (e.g. after they drop PHP4 compatibility)
 *
 * @author Aleksander Chrabaszcz (GreeK)
 */
class NotePurifier
{
  public function __construct()
  {
  }

  public function purify($text)
  {
    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
    $config->set('CSS.AllowTricky', true);
    $config->set('CSS.Trusted', true);
    $config->set('Attr.EnableID', true);
    $config->set('Attr.DefaultImageAlt', "");
    $config->set('Cache.SerializerPath', _ROOT_LOC . "/cache/HTMLPurifier");

    $config->set('Attr.IDBlacklist', array(
      'datetime', 'encodingSel', 'encodingSelection'
    ));

    $cleanedText = "";

    /* extract CSS styles from the note body - they must be cleaned separately */
    $cleanedCssStyles = "";
    while (preg_match('|<style( type="text/css")?>(.*?)</style>|s', $text, $matches)) {
      $text = str_replace($matches[0], "", $text);

      $css = new CantrCSSTidy();

      // replace default optimiser implementation by our own subclass
      // that performs additional whitelist-based css selector validation (xss-killer)
      $css->optimise = new CSSTidyOptimiseValidationExtension($css); // see lib/3rdparty/CSSTidy/readme.txt

      // these optimizations not important enough to confuse people
      $css->set_cfg("compress_colors", false);
      $css->set_cfg("compress_font-weight", false);

      // xss attacks security
      $css->set_cfg("discard_invalid_selectors", true);
      $css->set_cfg("discard_invalid_properties", true);

      $css->parse($matches[2]);
      $css->import = array(); // remove @imports from css styles, as they are disallowed

      $cleanedCssStyles .= $css->print->plain() . "\n";
    }
    // create one big CSS stylesheet, it will be placed on the top of the note body
    if (!empty($cleanedCssStyles)) {
      $cleanedText .= "<style type=\"text/css\">\n" . $cleanedCssStyles . "</style>";
    }

    $purifier = new HTMLPurifier($config);

    /* The only two allowed situations are:
     * </pre> on the beginning and <pre> at the end of note
     * no occurrences of <pre> at all
     * It's the only way to make htmlpuririer work - it will remove any complicated tags nested in <pre> */

    $hasPre = strstr($text, "</pre>");
    $isPreTag = !empty($hasPre);
    if ($isPreTag) {
      $toClean = "<pre>" . $text . "</pre>";
    } else {
      $toClean = $text;
    }

    // main function
    $cleanHtml = $purifier->purify($toClean);

    /* If we temporarily added <pre> and </pre> at the beginning and the end, then we must remove them now */
    if ($isPreTag) {
      $cleanHtml = substr($cleanHtml, 5, -6);
    }

    // here we join cleaned html to css
    $cleanedText .= $cleanHtml;

    return $cleanedText;
  }
}
