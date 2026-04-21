<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController
{
    public function index(Request $request)
    {
        $q       = trim((string) $request->query('q', ''));
        $results = [];

        if ($q !== '') {
            $shop   = require resource_path('shop.php');
            $needle = mb_strtolower($q);

            foreach ($shop['products'] as $catSlug => $list) {
                $catName = $shop['categories'][$catSlug]['name'] ?? $catSlug;
                foreach ($list as $p) {
                    $hay = mb_strtolower(
                        $p['name'] . ' ' .
                        ($p['shortDesc'] ?? '') . ' ' .
                        ($p['desc'] ?? '')
                    );
                    if (str_contains($hay, $needle)) {
                        $results[] = [...$p, 'category_slug' => $catSlug, 'category_name' => $catName];
                    }
                }
            }
        }

        return view('user.search', [
            'q'       => $q,
            'results' => $results,
        ]);
    }
}
