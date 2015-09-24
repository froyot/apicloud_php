### API Cloud PHP SDK(非官方，个人版)



### 文件目录

#### [ApiCloudDb.php  主要文件](apicloud/ApiCloudDb.php)

#### [QueryBuild.php 查询对象文件](apicloud/QueryBuilder.php)

#### [Networking.php 网络请求文件](network/Network.php)


### 使用说明

*   $apiCloud->createObj( $tableName, $data),创建对象
*   $res = $apiCloud->findOne($tableName, $objectId);查询对象
*   $res = $apiCloud->update( $tableName, $objectId, $data );更新对象
*   $res = $apiCloud->delete( $tableName, $objectId, $data );删除对象

*    $res = $apiCloud->createUser($data);注册用户
*    $res = $apiCloud->login($username, $password);用户登录
*    $res = $apiCloud->updateUser( $authId, $userId, $data );更新用户数据
*    $res = $apiCloud->verifyEmail( $data['username'], $data['email'] );向用户邮箱发验证邮件
*    $res = $apiCloud->sendResetEmail( $data['username'], $data['email'] );发送重置密码邮箱
*    $res = $apiCloud->logout( $authId );用户退出

*   查询
```
$query = new QueryBuilder();
$query->select(['sex','age'])->from('myobj')->where(['test'=>'1']);
$apiCloud = new ApiCloudDb();
$res = $apiCloud->findAll($query);
```

