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
                            "class" => "DepictionMarkdownView",
                            "markdown" => $this->description ?? "No description available.",
                            "useSpacing" => true,
                            "useRawFormat" => true,
                        ],
                    ],
                ],
                [
                    "tabname" => "Changelog",
                    "class" => "DepictionStackView",
                    "views" => $this->changeLogs->map(function ($changelog) {
                        return [
                            "class" => "DepictionSubheaderView",
                            "title" => $changelog->version,
                            "markdown" => $changelog->description,
                        ];
                    })->toArray(),
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
                            "title" => "Repo",
                            "action" => $this->homepage ?? url("/"),
                            "class" => "DepictionTableButtonView",
                        ],
                    ],
                ],
            ],
        ];
    }
}
