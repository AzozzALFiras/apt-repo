<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SileoResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            "class" => "DepictionTabView",
            "minVersion" => "0.1",
            "tintColor" => "#0f5187",
            "tabs" => [
                [
                    "tabname" => "Details",
                    "class" => "DepictionStackView",
                    "views" => [
                        [
                            "class" => "DepictionHeaderView",
                            "title" => $this->name . " " . $this->version,
                        ],
                        [
                            "class" => "DepictionSubheaderView",
                            "title" => "By " . ($this->author ?? "Unknown"),
                        ],
                        [
                            "class" => "DepictionScreenshotsView",
                            "itemSize" => "{160, 284}",
                            "screenshots" => [
                                [
                                    "accessibilityText" => $this->name,
                                    "url" => $this->icon_url_full,
                                    "fullSizeURL" => $this->icon_url_full,
                                ]
                            ],
                        ],
                        [
                            "class" => "DepictionMarkdownView",
                            "markdown" => $this->description ?? "No description available.",
                            "useSpacing" => true,
                            "useRawFormat" => true,
                        ],
                        [
                            "class" => "DepictionSeparatorView"
                        ],
                        [
                            "class" => "DepictionTableTextView",
                            "title" => "Version",
                            "text" => $this->version,
                        ],
                        [
                            "class" => "DepictionTableTextView",
                            "title" => "Package ID",
                            "text" => $this->package,
                        ],
                        [
                            "class" => "DepictionTableTextView",
                            "title" => "Maintainer",
                            "text" => $this->maintainer ?? "N/A",
                        ],
                        [
                            "class" => "DepictionTableTextView",
                            "title" => "Architecture",
                            "text" => $this->architecture ?? "N/A",
                        ],
                        [
                            "class" => "DepictionTableTextView",
                            "title" => "Installed Size",
                            "text" => $this->formatted_size,
                        ],
                        [
                            "class" => "DepictionTableButtonView",
                            "title" => "Download .deb",
                            "action" => $this->deb_file_url,
                        ],
                    ],
                ],
                [
                    "tabname" => "Changelog",
                    "class" => "DepictionStackView",
                    "views" => $this->changeLogs->map(function ($changelog) {
                        return [
                            [
                                "class" => "DepictionSubheaderView",
                                "title" => $changelog->version,
                            ],
                            [
                                "class" => "DepictionMarkdownView",
                                "markdown" => "<ul>" . collect(json_decode($changelog->changelog, true))->map(function ($item) {
                                    return "<li>" . e($item) . "</li>";
                                })->implode('') . "</ul>",
                            ],
                            [
                                "class" => "DepictionSeparatorView"
                            ],
                        ];
                    })->flatten(1)->toArray(), // flatten nested arrays
                ],
                [
                    "tabname" => "Follow Me",
                    "class" => "DepictionStackView",
                    "views" => [
                        [
                            "title" => "Twitter",
                            "action" => "https://twitter.com/" . ($this->author ?? "unknown"),
                            "class" => "DepictionTableButtonView",
                        ],
                    ],
                ],
                [
                    "tabname" => "Repo",
                    "class" => "DepictionStackView",
                    "views" => [
                        [
                            "title" => "Repo Homepage",
                            "action" => $this->homepage ?? url("/"),
                            "class" => "DepictionTableButtonView",
                        ],
                    ],
                ],
            ],
        ];
    }
}
