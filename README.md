1) `php artisan key:generate --ansi` <br><br>

2) Make directory for docker: <br>
   `mkdir ./storage/docker` <br>

3) Copy .env.example <br>
   `cp .env.example .env` <br>

4) Add host user to .env <br>
   `echo UID=$(id -u) >> .env` <br>
   `echo GID=$(id -g) >> .env` <br>

5) Run services docker <br>
   `docker-compose up -d --build` <br>

6) Install eslint, prettier <br>
   `npm install --save-dev @rushstack/eslint-patch` <br>
   `npm install --save-dev @vue/eslint-config-prettier` <br>
   `npm install --save-dev eslint` <br>
   `npm install --save-dev eslint-plugin-vue` <br>
   `npm install --save-dev prettier` <br>

7) `npm run lint` <br>

8) Install npm dependencies <br>
   `npm install` <br>
   `npm run dev` <br>
   `vite build` <br>
   `vite` <br>

9) Create link Storage
   `docker exec ring-php-app php artisan storage:link`<br>

10) Install Jetstream <br>
    `composer require laravel/jetstream` <br>
    `docker exec ring-php-app php artisan jetstream:install inertia --ssr --teams` <br>
    `npm install` <br>
    `npm run dev` <br>

11) Install Spatie <br>
    `composer require spatie/laravel-permission` <br>
    `docker exec ring-php-app php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"` <br>
    `docker exec ring-php-app php artisan optimize:clear` <br>
    `docker exec ring-php-app php artisan migrate`<br>
    `docker exec ring-php-app php artisan migrate:rollback`<br>
    `docker exec ring-php-app php artisan migrate` <br>
    `docker exec ring-php-app php artisan db:seed` <br>
    `// The User model requires this trait
    use HasRoles;`<br>

12) create seeders Role, User <br>
    `docker exec ring-php-app php artisan make:seeder RoleSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=RoleSeeder` <br>

13) Create resource UserResource, RoleResource, PermissionResource <br>
    `docker exec ring-php-app php artisan make:resource Admin/User/UserResource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Role/RoleResource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Permission/PermissionResource` <br>

14) Create resource controllers UserController,RoleController,PermissionController <br>
    `docker exec ring-php-app php artisan make:controller Admin/User/UserController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Role/RoleController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Permission/PermissionController --resource` <br>

15) Create requests RoleRequest, PermissionRequest <br>
    `docker exec ring-php-app php artisan make:request Admin/Role/RoleRequest` <br>
    `docker exec ring-php-app php artisan make:request Admin/Permission/PermissionRequest` <br>

16) Create resource UserSharedResource <br>
    `docker exec ring-php-app php artisan make:resource Admin/User/UserSharedResource` <br>

17) Create revoke controllers <br>
        `docker exec ring-php-app php artisan make:controller Admin/Invokable/RemovePermissionFromRoleController --invokable` <br>
        `docker exec ring-php-app php artisan make:controller Admin/Invokable/RemoveRoleFromUserController --invokable` <br>
        `docker exec ring-php-app php artisan make:controller Admin/Invokable/RemovePermissionFromUserController --invokable` <br>

18) Create model and migrations Rubric, Article <br>
    `docker exec ring-php-app php artisan make:model Admin/Rubric/Rubric -mf` <br>
    `docker exec ring-php-app php artisan make:model Admin/Article/Article -mf` <br>

19) Create migrations Many-to-Many relationship Rubric, Article <br>
    `docker exec ring-php-app php artisan make:migration create_article_has_rubrics_table` <br>
    `docker exec ring-php-app php artisan migrate`<br>
    `docker exec ring-php-app php artisan migrate:rollback`<br>
    `docker exec ring-php-app php artisan migrate` <br>

20) Create RubricSeeder, ArticleSeeder <br>
    `docker exec ring-php-app php artisan make:seeder RubricSeeder` <br>
    `docker exec ring-php-app php artisan make:seeder ArticleSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=RubricSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=ArticleSeeder` <br>

21) Create resource RubricResource, ArticleResource <br>
    `docker exec ring-php-app php artisan make:resource Admin/Rubric/RubricResource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Article/ArticleResource` <br>

22) Create resource controllers RubricController, ArticleController <br>
    `docker exec ring-php-app php artisan make:controller Admin/Rubric/RubricController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Article/ArticleController --resource` <br>

23) Create requests RubricRequest, ArticleRequest <br>
    `docker exec ring-php-app php artisan make:request Admin/Rubric/RubricRequest` <br>
    `docker exec ring-php-app php artisan make:request Admin/Article/ArticleRequest` <br>

24) Create revoke controllers Rubric and Article<br>
    `docker exec ring-php-app php artisan make:controller Admin/Invokable/RemoveArticleFromRubricController --invokable` <br>

25) npm install @mayasabha/ckeditor4-vue3

26) composer require unisharp/laravel-filemanager
    `php artisan vendor:publish --tag=lfm_config` <br>
    `php artisan vendor:publish --tag=lfm_public` <br>
    web.php: `Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
    });` <br>
    .env: `FILESYSTEM_DRIVER=public` <br>

27) Create ReportController, ChartController <br>
    `docker exec ring-php-app php artisan make:controller Admin/Report/ReportController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Chart/ChartController --resource` <br>

28) `npm install chart.js chartjs-adapter-moment` <br>

29) `npm install xlsx html2pdf.js jszip file-saver docx` <br>

30) Create resource controllers ComponentController, BuilderController, PageController, EditorController <br>
    `docker exec ring-php-app php artisan make:controller Admin/Component/ComponentController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Builder/BuilderController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Page/PageController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Editor/EditorController --resource` <br>

31) npm install codemirror @codemirror/lang-javascript @codemirror/state @codemirror/view @codemirror/theme-one-dark
 
32) npm install @fortawesome/vue-fontawesome @fortawesome/fontawesome-svg-core @fortawesome/free-solid-svg-icons

33) Create controller, resource, request, seeder model and migrations Setting, components Parameter <br>
    `docker exec ring-php-app php artisan make:model Admin/Setting/Setting -m` <br>
    `docker exec ring-php-app php artisan migrate` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Setting/SettingController --resource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Setting/SettingResource` <br>
    `docker exec ring-php-app php artisan make:request Admin/Setting/SettingRequest` <br>
    `docker exec ring-php-app php artisan make:seeder SettingSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=SettingSeeder` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Parameter/ParameterController --resource` <br>
    
34)  Create middleware ShareSettings <br>
    `docker exec ring-php-app php artisan make:resource Admin/Setting/SettingSharedResource` <br>

35) npm install vue-i18n@next <br>
36) npm install vue-draggable-next <br>
37) npm install roughjs  <br>
38) npm install @vueuse/head <br>
39) npm install @vue-flow/core @vue-flow/background @vue-flow/controls @vue-flow/minimap

40) `docker exec ring-php-app php artisan make:controller Admin/Diagram/DiagramController --resource` <br>

41) npm i flowchart <br> 
42) npm i vue-echarts-v3 <br> 

43) `docker exec ring-php-app php artisan make:model Admin/Page/Page -m` <br>
    `docker exec ring-php-app php artisan migrate` <br>
    `docker exec ring-php-app php artisan make:seeder PagesSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=PagesSeeder` <br>
    `docker exec ring-php-app php artisan make:factory PageFactory --model=Page` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Page/PageResource` <br>
    `docker exec ring-php-app php artisan make:request Admin/Page/PageRequest` <br>
44) `docker exec ring-php-app php artisan make:controller Api/Page/ApiPageController` <br>

45) `docker exec ring-php-app php artisan make:model Admin/Plugin/Plugin -mf` <br>
    `docker exec ring-php-app php artisan migrate` <br>
    `docker exec ring-php-app php artisan make:seeder PluginsSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=PluginsSeeder` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Plugin/PluginController --resource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Plugin/PluginResource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Plugin/PluginSharedResource` <br>
    `docker exec ring-php-app php artisan make:request Admin/Plugin/PluginRequest` <br>
    `docker exec ring-php-app php artisan make:controller Api/Plugin/ApiController --api` <br>

46) composer require "darkaonline/l5-swagger" <br>
    `docker exec ring-php-app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"` <br>
    `docker exec ring-php-app php artisan make:controller Api/Article/ApiArticleController --api` <br>
    `docker exec ring-php-app php artisan make:controller Api/Rubric/ApiRubricController --api` <br>
    `docker exec ring-php-app php artisan make:controller Api/User/ApiUserController --api` <br>
    `docker exec ring-php-app php artisan make:controller Api/Permission/ApiPermissionController --api` <br>
    `docker exec ring-php-app php artisan make:controller Api/Role/ApiRoleController --api` <br>
    `docker exec ring-php-app php artisan make:controller Api/Parameter/ApiParameterController --api` <br>
    `docker exec ring-php-app php artisan l5-swagger:generate` <br>
    `docker exec -it ring-php-app rm /var/www/public/storage` Удалите текущую символьную ссылку <br>
    `docker exec -it ring-php-app ln -s /var/www/storage /var/www/public/storage` Пересоздайте символьную ссылку <br>
    `docker exec -it ring-php-app ls -l /var/www/public/storage` Проверьте, правильно ли создана символьная ссылка <br>
    `docker exec -it ring-php-app ls -l /var/www/storage/api-docs/` Убедитесь, что права доступа к директории и файлу корректны <br>
    `docker exec -it --user root ring-php-app chmod -R 777 /var/www/storage/api-docs` Установите права доступа к папке <br>
    `docker exec -it --user root ring-php-app chmod -R 777 /var/www/storage/api-docs` Установите права доступа к папке <br>
    `docker exec -it ring-php-app ls -l /var/www/public/storage/api-docs/api-docs.json` Убедитесь, что файл api-docs.json доступен через веб-сервер <br>
    `docker exec -it ring-php-app ls /var/www/storage/api-docs/api-docs.json` После генерации проверьте наличие файла <br>
    `docker-compose restart` <br>
    `docker exec ring-php-app php artisan l5-swagger:generate` <br>

47) Удалите существующие символические ссылки <br>
    `docker exec -it ring-php-app rm /var/www/public/storage` <br>
    `docker exec -it ring-php-app rm /var/www/storage/api-docs` <br>
    Создайте новые символические ссылки внутри контейнера <br>
    `docker exec -it ring-php-app ln -s /var/www/storage/app/public /var/www/public/storage`  <br>
    `docker exec -it ring-php-app ln -s /var/www/storage/api-docs /var/www/public/storage/api-docs`  <br>
    Установите правильные права доступа <br>
    `docker exec -it --user root ring-php-app sh`  <br>
    `chmod -R 775 /var/www/storage/app/public`  <br>
    `chmod -R 775 /var/www/storage/api-docs`  <br>
    `exit`  <br>
    Скопируйте нужные файлы <br>
    `docker exec -it ring-php-app mkdir -p /var/www/public/vendor/swagger-api/swagger-ui/dist`  <br>
    `docker exec -it ring-php-app cp -r /var/www/vendor/swagger-api/swagger-ui/dist/. /var/www/public/vendor/swagger-api/swagger-ui/dist/`  <br>
    Очистите кэш и перезапустите контейнер:  <br>
    `docker exec -it ring-php-app php artisan cache:clear`  <br>
    `docker exec -it ring-php-app php artisan config:clear`  <br>
    `docker exec -it ring-php-app php artisan route:clear`  <br>
    `docker exec -it ring-php-app php artisan view:clear`  <br>
    `docker restart ring-php-app`  <br>
    `docker exec -it ring-php-app php artisan route:list`  <br>

48) `docker exec ring-php-app php artisan make:controller Public/Static/AboutController --resource` <br>
49) `docker exec ring-php-app php artisan make:controller Admin/About/SectionController --resource` <br>
    `docker exec ring-php-app php artisan make:model Admin/About/Section -m` <br>
    `docker exec ring-php-app php artisan make:resource Admin/About/SectionResource` <br>
    `docker exec ring-php-app php artisan make:request Admin/About/SectionRequest` <br>
    `docker exec ring-php-app php artisan make:seeder SectionSeeder` <br>
    `docker exec ring-php-app php artisan migrate` <br>
    `docker exec ring-php-app php artisan db:seed --class=SectionSeeder` <br>

50) `npm install date-fns` <br>

51) `docker exec ring-php-app php artisan make:controller Admin/System/SystemController` <br>

52) `docker exec ring-php-app php artisan make:model Admin/Comment/Comment -m`
    `docker exec ring-php-app php artisan migrate` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Comment/CommentController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Public/CommentController --resource` <br>
    `docker exec ring-php-app php artisan route:list` <br>
    `docker exec ring-php-app php artisan make:factory Admin/Comment/CommentFactory --model=Comment` <br>
    `docker exec ring-php-app php artisan make:seeder CommentsSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=CommentsSeeder` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Comment/CommentResource` <br>

53) `docker exec ring-php-app php artisan make:model User/Like/ArticleLike` <br>
    `docker exec ring-php-app php artisan make:migration create_article_likes_table --create=article_likes` <br>
    `docker exec ring-php-app php artisan migrate` <br>

54) `docker exec ring-php-app php artisan make:model Admin/Contact/Contact -m` <br> 
    `docker exec ring-php-app php artisan make:controller Admin/Contact/ContactController --resource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Contact/ContactResource` <br>
    `docker exec ring-php-app php artisan make:request Admin/Contact/ContactRequest` <br>
    `docker exec ring-php-app php artisan make:controller Public/Default/ContactController --resource` <br>
    `docker exec ring-php-app php artisan migrate` <br>
    `docker exec ring-php-app php artisan make:seeder ContactsSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=ContactsSeeder` <br>

55) Create model and migrations Tutorial, Guide, TutorialSeeder, GuideSeeder  <br>
    `docker exec ring-php-app php artisan make:model Admin/Tutorial/Tutorial -mf` <br>
    `docker exec ring-php-app php artisan make:model Admin/Guide/Guide -mf` <br>
    `docker exec ring-php-app php artisan make:migration create_guide_has_tutorials_table` <br>
    `docker exec ring-php-app php artisan migrate`<br>
    `docker exec ring-php-app php artisan make:seeder TutorialSeeder` <br>
    `docker exec ring-php-app php artisan make:seeder GuideSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=TutorialSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=GuideSeeder` <br>

56) Create TutorialResource, GuideResource,  TutorialController, GuideController, TutorialRequest, GuideRequest <br>
    `docker exec ring-php-app php artisan make:controller Admin/Tutorial/TutorialController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Guide/GuideController --resource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Tutorial/TutorialResource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Guide/GuideResource` <br>
    `docker exec ring-php-app php artisan make:request Admin/Tutorial/TutorialRequest` <br>
    `docker exec ring-php-app php artisan make:request Admin/Guide/GuideRequest` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Invokable/RemoveGuideFromTutorialController --invokable` <br>

57) Create Api Controllers
    `docker exec ring-php-app php artisan make:controller Api/Guide/ApiGuideController --api` <br>
    `docker exec ring-php-app php artisan make:controller Api/Tutorial/ApiTutorialController --api` <br>
    `docker exec ring-php-app php artisan l5-swagger:generate` <br>

58) Create model and migrations Category, Product, CategorySeeder, ProductSeeder  <br>
    `docker exec ring-php-app php artisan make:model Admin/Category/Category -mf` <br>
    `docker exec ring-php-app php artisan make:model Admin/Product/Product -mf` <br>
    `docker exec ring-php-app php artisan migrate`<br>
    `docker exec ring-php-app php artisan make:seeder CategorySeeder` <br>
    `docker exec ring-php-app php artisan make:seeder ProductSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=CategorySeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=ProductSeeder` <br>

59) Create TutorialResource, ProductResource,  CategoryController, ProductController, CategoryRequest, ProductRequest <br>
    `docker exec ring-php-app php artisan make:controller Admin/Category/CategoryController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Product/ProductController --resource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Category/CategoryResource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Product/ProductResource` <br>
    `docker exec ring-php-app php artisan make:request Admin/Category/CategoryRequest` <br>
    `docker exec ring-php-app php artisan make:request Admin/Product/ProductRequest` <br>
    `docker exec ring-php-app php artisan make:controller Api/Category/ApiCategoryController --api` <br>
    `docker exec ring-php-app php artisan make:controller Api/Product/ApiProductController --api` <br>
60) `docker exec ring-php-app php artisan make:migration create_product_related_products_table --create=product_related_products` <br>
    `docker exec ring-php-app php artisan migrate` <br>

61) npm install highlight.js

62) `docker exec ring-php-app php artisan make:migration create_product_variants_table` <br>
    `docker exec ring-php-app php artisan make:model Admin/Product/ProductVariant` <br>
    `docker exec ring-php-app php artisan make:seeder ProductVariantSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=ProductVariantSeeder` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Product/ProductVariantResource` <br>
    `docker exec ring-php-app php artisan make:migration create_product_images_table` <br>
    `docker exec ring-php-app php artisan migrate` <br>
    `docker exec ring-php-app php artisan make:seeder ProductImageSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=ProductImageSeeder` <br>
    `docker exec ring-php-app php artisan make:model Admin/Product/ProductImage` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Product/ProductImageResource` <br>
    `docker exec ring-php-app php artisan make:request Admin/Product/ProductImageRequest` <br>

63) `docker exec ring-php-app php artisan make:migration create_property_groups_table --create=property_groups` <br>
    `docker exec ring-php-app php artisan make:migration create_properties_table --create=properties` <br>
    `docker exec ring-php-app php artisan make:migration create_property_data_table --create=property_data` <br>
    `docker exec ring-php-app php artisan make:migration create_category_has_property_table --create=category_has_property` <br>
    `docker exec ring-php-app php artisan make:migration create_product_has_properties_table --create=product_has_properties` <br>
    `docker exec ring-php-app php artisan migrate` <br>
    `docker exec ring-php-app php artisan make:model Admin/Property/PropertyGroup` <br>
    `docker exec ring-php-app php artisan make:model Admin/Property/Property` <br>
    `docker exec ring-php-app php artisan make:model Admin/Property/PropertyData` <br>
    `docker exec ring-php-app php artisan make:model Admin/Product/ProductProperty` <br>
    `docker exec ring-php-app php artisan make:seeder PropertyGroupSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=PropertyGroupSeeder` <br>
    `docker exec ring-php-app php artisan make:seeder PropertySeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=PropertySeeder` <br>
    `docker exec ring-php-app php artisan make:seeder PropertyDataSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=PropertyDataSeeder` <br>
    `docker exec ring-php-app php artisan make:seeder ProductPropertySeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=ProductPropertySeeder` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Property/PropertyGroupController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Property/PropertyController --resource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Property/PropertyGroupResource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Property/PropertyResource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Property/PropertyDataResource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Product/ProductPropertyResource` <br>
    `docker exec ring-php-app php artisan make:request Admin/Property/PropertyGroupRequest` <br>
    `docker exec ring-php-app php artisan make:request Admin/Property/PropertyRequest` <br>
    `docker exec ring-php-app php artisan make:request Admin/Product/ProductPropertyRequest` <br>

