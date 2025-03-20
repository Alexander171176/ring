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

30) Create resource controllers ComponentController, BuilderController, EditorController <br>
    `docker exec ring-php-app php artisan make:controller Admin/Component/ComponentController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Builder/BuilderController --resource` <br>
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

43) `docker exec ring-php-app php artisan make:model Admin/Plugin/Plugin -mf` <br>
    `docker exec ring-php-app php artisan migrate` <br>
    `docker exec ring-php-app php artisan make:seeder PluginsSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=PluginsSeeder` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Plugin/PluginController --resource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Plugin/PluginResource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Plugin/PluginSharedResource` <br>
    `docker exec ring-php-app php artisan make:request Admin/Plugin/PluginRequest` <br>
    `docker exec ring-php-app php artisan make:controller Api/Plugin/ApiController --api` <br>

44) composer require "darkaonline/l5-swagger" <br>
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

45) Удалите существующие символические ссылки <br>
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

46) `npm install date-fns` <br>

47) `docker exec ring-php-app php artisan make:controller Admin/System/SystemController` <br>

48) `docker exec ring-php-app php artisan make:model Admin/Comment/Comment -m`
    `docker exec ring-php-app php artisan migrate` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Comment/CommentController --resource` <br>
    `docker exec ring-php-app php artisan make:controller Public/CommentController --resource` <br>
    `docker exec ring-php-app php artisan route:list` <br>
    `docker exec ring-php-app php artisan make:factory Admin/Comment/CommentFactory --model=Comment` <br>
    `docker exec ring-php-app php artisan make:seeder CommentsSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=CommentsSeeder` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Comment/CommentResource` <br>

49) `docker exec ring-php-app php artisan make:model User/Like/ArticleLike` <br>
    `docker exec ring-php-app php artisan make:migration create_article_likes_table --create=article_likes` <br>
    `docker exec ring-php-app php artisan migrate` <br>

50) npm install highlight.js

____________________________

51) Create middleware SetLocaleFromSettings <br>
    `docker exec ring-php-app php artisan make:middleware SetLocaleFromSettings` <br>

52) Create RubricController <br>
    `docker exec ring-php-app php artisan make:controller Public/Default/RubricController` <br>

53) Create migration Tags, Images <br>
    `docker exec ring-php-app php artisan make:model Admin/Article/Tag -mf` <br>
    `docker exec ring-php-app php artisan make:migration create_article_has_tag_table --create=article_has_tag` <br>
    `docker exec ring-php-app php artisan make:model Admin/Article/ArticleImage -mf` <br>
    `docker exec ring-php-app php artisan make:migration create_article_has_images_table --create=article_has_images` <br>
    
54) Create resource Tag, ArticleImage
    `docker exec ring-php-app php artisan make:resource Admin/Article/TagResource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Article/ArticleImageResource` <br>
 
55)  Create request Tag, ArticleImage
    `docker exec ring-php-app php artisan make:request Admin/Article/TagRequest` <br>
     `docker exec ring-php-app php artisan make:request Admin/Article/ArticleImageRequest` <br>
 
56) Create seed Tag, ArticleImage
    `docker exec ring-php-app php artisan make:seeder TagSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=TagSeeder` <br>
    `docker exec ring-php-app php artisan make:seeder ArticleImageSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=ArticleImageSeeder` <br>
 
58) Create model and migrations Section <br>
    `docker exec ring-php-app php artisan make:model Admin/Section/Section -mf` <br>
    `docker exec ring-php-app php artisan make:migration create_rubric_has_sections_table --create=rubric_has_sections` <br>
    `docker exec ring-php-app php artisan migrate`<br>
    `docker exec ring-php-app php artisan make:seeder SectionSeeder` <br>
    `docker exec ring-php-app php artisan db:seed --class=SectionSeeder` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Section/SectionController --resource` <br>
    `docker exec ring-php-app php artisan make:resource Admin/Section/SectionResource` <br>
    `docker exec ring-php-app php artisan make:request Admin/Section/SectionRequest` <br>
    `docker exec ring-php-app php artisan make:controller Admin/Invokable/RemoveRubricFromSectionController --invokable` <br>
