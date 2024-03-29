<?php declare(strict_types = 1);

namespace MailPoet\EmailEditor\Engine;

if (!defined('ABSPATH')) exit;


use WP_Theme_JSON;
use WP_Theme_JSON_Resolver;

/**
 * E-mail editor works with own theme.json which defines settings for the editor and styles for the e-mail.
 * This class is responsible for accessing data defined by the theme.json.
 */
class ThemeController {
  private WP_Theme_JSON $themeJson;

  public function getTheme(): WP_Theme_JSON {
    if (isset($this->themeJson)) {
      return $this->themeJson;
    }
    $coreThemeData = WP_Theme_JSON_Resolver::get_core_data();
    $themeJson = (string)file_get_contents(dirname(__FILE__) . '/theme.json');
    $themeJson = json_decode($themeJson, true);
    /** @var array $themeJson */
    $coreThemeData->merge(new WP_Theme_JSON($themeJson, 'default'));
    $this->themeJson = apply_filters('mailpoet_email_editor_theme_json', $coreThemeData);
    return $this->themeJson;
  }

  public function getSettings(): array {
    $emailEditorThemeSettings = $this->getTheme()->get_settings();
    $siteThemeSettings = WP_Theme_JSON_Resolver::get_theme_data()->get_settings();
    $emailEditorThemeSettings['color']['palette']['theme'] = [];
    if (isset($siteThemeSettings['color']['palette']['theme'])) {
      $emailEditorThemeSettings['color']['palette']['theme'] = $siteThemeSettings['color']['palette']['theme'];
    }
    return $emailEditorThemeSettings;
  }

  public function getStylesheetForRendering(): string {
    $emailThemeSettings = $this->getSettings();

    $cssPresets = '';
    // Font family classes
    foreach ($emailThemeSettings['typography']['fontFamilies']['default'] as $fontFamily) {
      $cssPresets .= ".has-{$fontFamily['slug']}-font-family { font-family: {$fontFamily['fontFamily']}; } \n";
    }
    // Font size classes
    foreach ($emailThemeSettings['typography']['fontSizes']['default'] as $fontSize) {
      $cssPresets .= ".has-{$fontSize['slug']}-font-size { font-size: {$fontSize['size']}; } \n";
    }
    // Color palette classes
    $colorDefinitions = array_merge($emailThemeSettings['color']['palette']['theme'], $emailThemeSettings['color']['palette']['default']);
    foreach ($colorDefinitions as $color) {
      $cssPresets .= ".has-{$color['slug']}-color { color: {$color['color']}; } \n";
      $cssPresets .= ".has-{$color['slug']}-background-color { background-color: {$color['color']}; } \n";
    }

    // Block specific styles
    $cssBlocks = '';
    $blocks = $this->getTheme()->get_styles_block_nodes();
    foreach ($blocks as $blockMetadata) {
      $cssBlocks .= $this->getTheme()->get_styles_for_block($blockMetadata);
    }

    return $cssPresets . $cssBlocks;
  }

  public function translateSlugToFontSize(string $fontSize): string {
    $settings = $this->getSettings();
    foreach ($settings['typography']['fontSizes']['default'] as $fontSizeDefinition) {
      if ($fontSizeDefinition['slug'] === $fontSize) {
        return $fontSizeDefinition['size'];
      }
    }
    return $fontSize;
  }

  public function translateSlugToColor(string $colorSlug): string {
    $settings = $this->getSettings();
    $colorDefinitions = array_merge($settings['color']['palette']['theme'], $settings['color']['palette']['default']);
    foreach ($colorDefinitions as $colorDefinition) {
      if ($colorDefinition['slug'] === $colorSlug) {
        return strtolower($colorDefinition['color']);
      }
    }
    return $colorSlug;
  }
}
