<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\Article */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '回收站';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-index">
    <div class="box box-success">
        <div class="box-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    'id',
                    'title',
                    'category',
                    'status:boolean',
                    // 'author',
                    // 'created_at',
                     'deleted_at:date',
                    // 'status',
                    // 'cover',

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{update} {delete}',
                        'buttons' => [
                            'update' => function($url, $model) {
                                return '还原';
                            },
                            'delete' => function($url, $model) {
                                return Html::a('清除',['hard-delete', 'id' => $model->id]);
                            }
                        ]
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
