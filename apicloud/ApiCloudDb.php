<?php
/**
 * API Cloud php sdk
 * @author wangxianlong <xianlong300@sian.com>
 */
namespace app\utils\apicloud;
use app\utils\network\Network;

class ApiCloudDb
{
    /**
     * appid
     * @var string
     */
    public $appId ='';

    /**
     * appkey
     * @var string
     */
    public $appKey = '';

    /**
     * 请求url
     * @var string
     */
    private $url;

    /**
     * 请求方式
     * @var string
     */
    private $method;

    /**
     * 网络请求类
     * @var string
     */
    private $netClient;

    /**
     * 请求头
     * @var array
     */
    private $header = [];

    /**
     * api 域名
     */
    const API_CLOUD_URL = 'https://d.apicloud.com';

    function __construct( $appId, $appKey )
    {
        $this->appId = $appId;
        $this->appKey = $appKey;

        list($tmp1, $tmp2) = explode(' ', microtime());
        $time =  (float)sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);

        $headerKey = sha1($this->appId.'UZ'.$this->appKey.'UZ'.$time).'.'.$time;
        $this->netClient = new Network();
        $this->header[] = "X-APICloud-AppId:{$this->appId}";
        $this->header[] = "X-APICloud-AppKey:{$headerKey}";
        $this->header[] = "Content-Type:application/json";
    }

    /**
     * 创建一个对象
     * @param  string $table 表名
     * @param array $data 数据
     * @return array      对象数据
     */
    public function createObj( $table, $data = [] )
    {
        $this->url =  ApiCloudDb::API_CLOUD_URL.'/mcm/api/'.$table;
        $this->method = 'POST';
        if( $data )
        {
            $data = json_encode( $data );
            $this->netClient->setOption( CURLOPT_POSTFIELDS, $data );
        }
        $res = $this->execute();
        if( $res && $data = json_decode( $res, true ) )
        {
            return $data;
        }
        return null;
    }

     /**
     * 获取对象
     * @param  string $table 表名
     * @param array $data 数据
     * @return array      对象数据
     */
    public function findOne( $table, $objectId )
    {
        $this->url =  ApiCloudDb::API_CLOUD_URL.'/mcm/api/'.$table.'/'.$objectId;
        $this->method = 'GET';
        $res = $this->execute();
        if( $res && $data = json_decode( $res, true ) )
        {
            return $data;
        }
        return null;
    }

    /**
     * 更新对象
     * @param  string $table    数据表
     * @param  string  $objectId 对象id
     * @param  array $data     对象数据
     * @return $data           对象数据
     */
    public function update( $table, $objectId, $data = [] )
    {
        $this->url =  ApiCloudDb::API_CLOUD_URL.'/mcm/api/'.$table.'/'.$objectId;
        $this->method = 'PUT';
        if( $data )
        {
            $data = json_encode( $data );
            $this->netClient->setOption( CURLOPT_POSTFIELDS, $data );
        }
        $res = $this->execute();
        if( $res && $data = json_decode( $res, true ) )
        {
            return $data;
        }
        return null;
    }

    /**
     * 删除对象
     * @param  string $table    表名
     * @param  string $objectId 对象id
     * @return boolean           是否删除
     */
    public function delete( $table, $objectId )
    {
        $this->url =  ApiCloudDb::API_CLOUD_URL.'/mcm/api/'.$table.'/'.$objectId;
        $this->method = 'DELETE';
        $res = $this->execute();
        $data = json_decode( $res, true );
        if( $res && is_array( $data ) && count($data) == 0 )
        {
            return true;
        }

        return false;
    }


    /**
     * 批量操作
     *
     * ```php
     * [
     *     "request":[
     *         [
     *             "method" => "POST",
     *             "path" => "/mcm/api/company",
     *             "body"=>[
     *                 "name" => "apicloud",
     *                 "address" => "北京市..."
     *              ]
     *         ],
     *         [
     *             "method" => "POST",
     *             "path" => "/mcm/api/company",
     *             "body"=>[
     *                 "name" => "apicloud",
     *                 "address" => "北京市..."
     *              ]
     *         ],
     *     ]
     *
     * ]
     *
     * ```
     * @param  array $data 批量操作数组
     * @return [type]       [description]
     */
    public function batch( $data )
    {
        $this->method = 'POST';
        $this->netClient->setOption( CURLOPT_POSTFIELDS, $data );
        $res =  $this->execute();
        if( $res && json_decode( $res,true) )
        {
            return json_decode( $res, true );
        }
        return [];
    }

    ///////////////////////////对象原子操作////////////////////////////////////

    /**
     * 对某个字段进行自增，自乘操作
     * @param  string $table    表名
     * @param  string $objectId 对象id
     * @param  string $column   字段名称
     * @param   string $tic      操作符号以及操作值
     * @return [type]           [description]
     */
    public function tic( $table, $objectId, $column, $tic )
    {
        if( strlen($tic) < 2 )
            return null;
        $num = substr($tic, 1);
        $tic = substr($tic, 0, 1);
        if(!in_array($tic,['+','x']))
        {
            return null;
        }
        switch($tic)
        {
            case '+':$key = '$inc';break;
            case 'x':$key = '$mul';break;
        }
        $this->url =  ApiCloudDb::API_CLOUD_URL.'/mcm/api/'.$table.'/'.$objectId;
        $this->method = 'PUT';
        $data [$key] = [$column=>$num];
        $data = json_encode( $data );
        $this->netClient->setOption( CURLOPT_POSTFIELDS, $data );
        $res = $this->execute();
        if( $res && $data = json_decode( $res, true ) )
        {
            return $data;
        }
        return null;
    }

    /**
     * 根据键值对设置列
     * @param [type] $table    [description]
     * @param [type] $objectId [description]
     * @param [type] $data     [description]
     */
    public function set( $table, $objectId, $data )
    {
        $this->url =  ApiCloudDb::API_CLOUD_URL.'/mcm/api/'.$table.'/'.$objectId;
        $this->method = 'PUT';
        $data = ['$set'=>$data];
        $data = json_encode( $data );
        $this->netClient->setOption( CURLOPT_POSTFIELDS, $data );
        $res = $this->execute();
        if( $res && $data = json_decode( $res, true ) )
        {
            return $data;
        }
        return null;
    }


    ///////////////////////////查询操作////////////////////////////////////////

    /**
     * 数据查询操作
     * @param  object $query 查询query对象
     * @return array        数据数组
     */
    public function findAll($query)
    {
        $this->url =  ApiCloudDb::API_CLOUD_URL.'/mcm/api/'.$query->getForm();
        $filter = $query->build();
        if($filter)
        {
            $this->url .= '?filter='.json_encode( $filter );
        }
        $this->method = 'GET';
        $res = $this->execute();
        if( $res && $data = json_decode( $res, true ) )
        {
            return $data;
        }
        return null;
    }



    ///////////////////////////用户模块////////////////////////////////////////
    /**
     * 新增用户
     * @param  array  $data 用户数据，必须有username,password字段
     * @return array       用户数据
     */
    public function createUser( $data )
    {
        $this->url =  ApiCloudDb::API_CLOUD_URL.'/mcm/api/user';
        $this->method = 'POST';

        if( $data && isset( $data['username'] ) && isset( $data['password'] ) )
        {
            $data = json_encode( $data );
            $this->netClient->setOption( CURLOPT_POSTFIELDS, $data );
        }
        else
        {
            return null;
        }
        $res = $this->execute();
        $data = json_decode( $res, true );
        if( $res && $data )
        {
            return $data;
        }
        return null;
    }

    /**
     * 对已经存在的用户进行邮箱验证
     * @param  string $userName  用户名
     * @param  string $userEmail 用户邮箱
     * @return boolean            是否成功
     */
    public function verifyEmail( $userName, $userEmail )
    {
        $this->url =  ApiCloudDb::API_CLOUD_URL.'/mcm/api/user/verifyEmail';
        $this->method = 'POST';
        if( $userName && $userEmail )
        {
            $data = json_encode([
                'username'=>$userName,
                'email'=>$userEmail,
                "language"=>"zh_CN"
                ]);
            $this->netClient->setOption( CURLOPT_POSTFIELDS, $data );
        }
        else
        {
            return false;
        }
        $res = $this->execute();
        $data = json_decode( $res, true );
        if( $res && is_array( $data ) && isset( $data['status'] ) && $data['status'] )
        {
            return true;
        }
        return false;
    }

    /**
     * 发送重置密码邮件
     * @param  string $userName  用户名
     * @param  string $userEmail 用户邮箱
     * @return boolean            是否成功
     */
    public function sendResetEmail( $userName, $userEmail )
    {
        $this->url =  ApiCloudDb::API_CLOUD_URL.'/mcm/api/user/resetRequest';
        $this->method = 'POST';
        if( $userName && $userEmail )
        {
            $data = json_encode([
                'username'=>$userName,
                'email'=>$userEmail,
                "language"=>"zh_CN"
                ]);
            $this->netClient->setOption( CURLOPT_POSTFIELDS, $data );
        }
        else
        {
            return false;
        }
        $res = $this->execute();
        $data = json_decode( $res, true );
        if( $res && is_array( $data ) && isset($data['status']) && $data['status'] )
        {
            return true;
        }
        return false;

    }

    /**
     * 更新用户信息
     * @param  string $authId 登陆token
     * @param  string $userId 用户id
     * @param  array $data   用户信息数组
     * @return [type]         [description]
     */
    public function updateUser( $authId, $userId, $data )
    {
        $this->header[] = "authorization:".$authId;
        $res = $this->update( 'user', $userId, $data );
        return $res;
    }

    /**
     * 删除用户
     * @param  string $authId 登陆token
     * @param  string $userId 用户id
     * @return [type]         [description]
     */
    public function deleteUser( $authId, $userId )
    {
        $this->header[] = "authorization:".$authId;
        $res = $this->delete( $userId );
        return $res;
    }

    /**
     * 用户登陆
     * @param  [type] $userName [description]
     * @param  [type] $password [description]
     * @return [type]           [description]
     */
    public function login( $userName, $password )
    {
        $this->url =  ApiCloudDb::API_CLOUD_URL.'/mcm/api/user/login';
        $this->method = 'POST';
        if( $userName && $password )
        {
            $data = json_encode([
                'username'=>$userName,
                'password'=>$password
                ]);
            $this->netClient->setOption( CURLOPT_POSTFIELDS, $data );
        }
        else
        {
            return null;
        }
        $res = $this->execute();
        $data = json_decode( $res, true );
        if( $res && is_array( $data ) && isset( $data['userId'] ) && $data['userId'] )
        {
            return $data;
        }
        return null;
    }

    /**
     * 用户退出
     * @param  [type] $userName [description]
     * @param  [type] $password [description]
     * @return [type]           [description]
     */
    public function logout( $authId )
    {
        $this->header[] = "authorization:".$authId;
        $this->url =  ApiCloudDb::API_CLOUD_URL.'/mcm/api/user/logout';
        $this->method = 'POST';
        $res = $this->execute();
        $data = json_decode( $res, true );
        if( $res && is_array( $data ) && count( $data ) == 0 )
        {
            return true;
        }
        return false;
    }


    ////////////////////////角色/////////////////////////////////////////

    /**
     * 创建角色
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function createRole($data)
    {
        if( !( isset($data['name'] ) && isset( $data['description'] ) ) )
        {
            return null;
        }
        return $this->createObj( 'role', $data );
    }

    /**
     * 获取角色
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getRole( $id )
    {
        return $this->findOne( 'role', $id );
    }

    /**
     * 更新角色
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function updateRole( $data )
    {
        return $this->update( 'role', $data );
    }

    /**
     * 删除角色
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function deleteRole( $data )
    {
        return $this->delete( 'role', $data );
    }

    /**
     * 解析网络请求
     * @return [type] [description]
     */
    public function execute()
    {
        $this->netClient->setOption( CURLOPT_HTTPHEADER, $this->header );

        $this->netClient->setOption( CURLOPT_SSL_VERIFYPEER, false );
        $this->netClient->setOption( CURLOPT_SSL_VERIFYHOST, 2 );
        if( $this->method == 'POST' )
        {
            return $this->netClient->post( $this->url );
        }
        elseif( $this->method == 'GET' )
        {
            return $this->netClient->get( $this->url );
        }
        elseif( $this->method == 'PUT' )
        {
            return $this->netClient->put( $this->url );
        }
        elseif( $this->method == 'DELETE' )
        {
            return $this->netClient->delete($this->url);
        }

    }
}
