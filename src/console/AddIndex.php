<?php

namespace Antsfree\Mxusearch\Console;

use Antsfree\Mxusearch\Mxusearch;
use Illuminate\Console\Command;
use MXU\Content\Models\Article;
use MXU\Content\Models\Column;
use MXU\Content\Models\ContentPublish;
use MXU\Terminal\Models\Site;

class AddIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mxusearch:index {publish_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new mxusearch index by ids.';

    /**
     * AddIndex constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $publish_id = $this->argument('publish_id');
        $published  = ContentPublish::find($publish_id);
        if (!$published) {
            echo '没有发布记录';
        }
        $site_title  = Site::siteInfo('title');
        $column_name = Column::columnInfo('column_name');
        $index       = [
            'id'              => $published->id,
            'order_id'        => $published->order_id,
            'site_id'         => $published->site_id,
            'site_info'       => $site_title[$published->site_id],
            'column_id'       => $published->column_id,
            'column_name'     => $column_name[$published->column_id],
            'origin_id'       => $published->origin_id,
            'type'            => $published->type,
            'title'           => $published->title,
            'title_attribute' => $published->title_attribute,
            'index_pic'       => $published->index_pic ? get_image_by_key($published->index_pic) : '',
            'author'          => $published->author,
            'brief'           => $published->brief,
            'keywords'        => $published->keywords,
            'subtitle'        => $published->subtitle,
            'source'          => $published->source,
            'source_link'     => $published->source_link,
            'publish_time'    => $published->publish_time,
            'content'         => '',
        ];

        if ($published->type == 'article') {
            $article = Article::find($published->origin_id);
            $index['content'] = strip_tags($article->content);
        }
        print_r($index);die;
        $ret = Mxusearch::addIndex($index);
        var_dump($ret);
//        var_dump($ret);
//        $a = Mxusearch::getCurrentTokenizer();
//        var_dump($a);
    }
}
