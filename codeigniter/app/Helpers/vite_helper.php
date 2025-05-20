<?php

if (!function_exists('vite_asset')) {
  /**
   * Gets the path to a Vite-built asset, resolving it through the manifest.
   *
   * @param string $entrypoint The entry point (e.g., 'src/main.ts' or 'src/main.js')
   * @param string $buildDir   The directory where Vite outputs built assets (relative to FCPATH . 'public/')
   * @return string HTML tags for the entry point's assets.
   */
  function vite_asset(string $entrypoint, string $buildDir = 'build'): string
  {
    static $manifest = null;
    static $manifestPath = '';

    $currentBuildDir = FCPATH . $buildDir; // FCPATH points to your project root (where 'app', 'public', 'system' are)
    $currentManifestPath = $currentBuildDir . '/.vite/manifest.json';

    if ($manifest === null || $manifestPath !== $currentManifestPath) {
      if (!is_file($currentManifestPath)) {
        log_message('error', "Vite manifest not found at: {$currentManifestPath}");
        // In development, you might want to fall back to Vite's dev server
        // For simplicity, we'll just return an error or empty string for production.
        if (ENVIRONMENT === 'development') {
          // Assuming Vite dev server is running on 5173 and serving the entry directly
          // This part is for a more advanced setup where CI can also serve Vite dev assets.
          // For now, we'll focus on the build output.
          return '<script type="module" src="http://localhost:5173/@vite/client"></script>' .
            '<script type="module" src="http://localhost:5173/' . $entrypoint . '"></script>';
        }
        return "";
      }
      $manifest = json_decode(file_get_contents($currentManifestPath), true);
      $manifestPath = $currentManifestPath;
      if (json_last_error() !== JSON_ERROR_NONE) {
        log_message('error', "Error decoding Vite manifest: " . json_last_error_msg());
        return "";
      }
    }

    if (!isset($manifest[$entrypoint])) {
      log_message('error', "Vite entrypoint '{$entrypoint}' not found in manifest.");
      return "";
    }

    $entryData = $manifest[$entrypoint];
    $html = '';
    $basePath = base_url($buildDir . '/'); // base_url() gives your site's base URL

    // Add the main JS file
    if (isset($entryData['file'])) {
      $html .= '<script type="module" crossorigin src="' . $basePath . $entryData['file'] . '"></script>' . PHP_EOL;
    }

    // Add any CSS files associated with the entry point
    if (isset($entryData['css']) && is_array($entryData['css'])) {
      foreach ($entryData['css'] as $cssFile) {
        $html .= '<link rel="stylesheet" href="' . $basePath . $cssFile . '">' . PHP_EOL;
      }
    }

    // Add any imported JS modules (dynamic imports) - less common for initial load
    if (isset($entryData['dynamicImports']) && is_array($entryData['dynamicImports'])) {
      foreach ($entryData['dynamicImports'] as $importName) {
        if (isset($manifest[$importName]['file'])) {
          $html .= '<link rel="modulepreload" href="' . $basePath . $manifest[$importName]['file'] . '">' . PHP_EOL;
        }
      }
    }

    return $html;
  }
}
