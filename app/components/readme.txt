
Filter
    去除指定內容

Validate
    驗證指定內容

Bridge
    相同的接口
    橋接不同的物件
    達到切換使用其它 library 的目的

Generator
    可以設定參數
    使用 render() 來產生 文字 或 HTML

Helper
    完全使用靜態方法
    完全不需要實體化
    完全沒有要設定的值
    但是程式中可以會有已設定的 常數 做為判斷值
    直接使用 或 傳入特定參數後 回傳所需的資訊
    
Formatter
    代入指定類型的資料
    有各種不同格式化的輸出

Manager
    管理特定性質的資訊
    可以設定參數
    通常會有 __construct() or init() or set param 參數 之類的 method
    取得沿伸之後的功能或是回傳值

Identity
    與 Manager 相似
    但是有需要代入參數做認證
    認證前後所使用的 method 傳出值會不相同

Service
    第三方網站資源整合
    有可能需要認證
    有可能是事前已認證, 只要在程式中設定 API 相關 path, account, password...

Base
    一個用來被繼承用的 base class

ccHelper
    view helper only
