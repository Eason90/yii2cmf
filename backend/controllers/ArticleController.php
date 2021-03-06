<?php

namespace backend\controllers;

use backend\models\ArticleForm;
use common\models\ArticleData;
use common\models\ArticleTag;
use yidashi\webuploader\WebuploaderAction;
use Yii;
use common\models\Article;
use backend\models\search\Article as ArticleSearch;
use yii\base\DynamicModel;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class ArticleController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
    public function actions()
    {
        return [
            'upload' => [
                'class' => 'kucha\ueditor\UEditorAction',
                'config' => [
                    'imageUrlPrefix' => \Yii::getAlias('@static').'/', //图片访问路径前缀
                    'imagePathFormat' => 'upload/image/{yyyy}{mm}{dd}/{time}{rand:6}', //上传保存路径
                ],
            ],
        ];
    }
    /**
     * Lists all Article models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ArticleSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 回收站列表
     *
     * @return mixed
     */
    public function actionTrash()
    {
        $query = \common\models\Article::find()->onlyTrashed();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ]
        ]);
        return $this->render('trash',[
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * 还原
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionReduction()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $model = Article::find()->where(['id' => $id])->onlyTrashed()->one();
        if(!$model) {
            throw new NotFoundHttpException('文章不存在!');
        }
        $model->reduction();
        return [
            'message' => '操作成功'
        ];
    }

    /**
     * 彻底删除
     * @return array
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionHardDelete()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        $model = Article::find()->where(['id' => $id])->trashed()->one();
        if(!$model) {
            throw new NotFoundHttpException('文章不存在!');
        }
        $model->delete();
        return [
            'message' => '操作成功'
        ];
    }
    public function actionClear()
    {
        if (Article::deleteAll(['>', 'deleted_at', 0]) !== false) {
            Yii::$app->session->setFlash('success', '操作成功');
            return $this->redirect('trash');
        }
        throw new Exception('操作失败');
    }
    /**
     * Displays a single Article model.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Article model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ArticleForm();
        if ($model->load(Yii::$app->request->post()) && $model->store()) {
            return $this->redirect(['index']);
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }
    /**
     * Updates an existing Article model.
     * If update is successful, the browser will be redirected to the 'view' page.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = ArticleForm::findOne($id);
        if ($model->load(Yii::$app->request->post()) && $model->update()) {
            return $this->redirect(['index']);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }
    /**
     * Deletes an existing Article model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->softDelete();

        return $this->redirect(['index']);
    }


    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return Article the loaded model
     *
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Article::find()->where(['id' => $id])->normal()->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionChangeStatus()
    {
        Yii::$app->response->format = 'json';
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        $formModel = DynamicModel::validateData(['id' => $id, 'status' => $status], [
            [['id', 'status'], 'required']
        ]);
        if ($formModel->hasErrors()) {
            return ['status' => 0, 'msg' => current($formModel->getFirstErrors())];
        }
        $model = $this->findModel($id);
        if ($model->status == $status) {
            $model->status = 1- intval($status);
            $model->save();
        }
        return [
            'status' => 1
        ];
    }
}
