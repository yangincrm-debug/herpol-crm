<?php

return [

    /*
     * The disk on which to store added files and derived images by default. Choose
     * one or more of the disks you've configured in config/filesystems.php.
     */
    'disk_name' => env('MEDIA_DISK', 's3'), // <-- MinIO için S3 yaptık

    /*
     * The maximum file size of an item in bytes.
     * Adding a larger file will result in an exception.
     */
    'max_file_size' => 1024 * 1024 * 100, // 100MB

    /*
     * This queue will be used to generate derived and responsive images.
     * Leave empty to use the default queue.
     */
    'queue_name' => '',

    /*
     * By default all conversions will be performed on a queue.
     */
    'queue_conversions_by_default' => env('QUEUE_CONNECTION', 'sync') !== 'sync',

    /*
     * The fully qualified class name of the media model.
     */
    'media_model' => Spatie\MediaLibrary\MediaCollections\Models\Media::class,

    /*
     * The fully qualified class name of the model used for temporary uploads.
     *
     * This model is only used in Media Library Pro (paid version).
     */
    'temporary_upload_model' => Spatie\MediaLibraryPro\Models\TemporaryUpload::class,

    /*
     * When enabled, Media Library Pro will only process temporary uploads that were uploaded
     * by the same user that is currently logged in.
     */
    'enable_temporary_uploads_session_affinity' => true,

    /*
     * When enabled, Media Library Pro will generate thumbnails for uploaded files.
     */
    'generate_thumbnails_for_temporary_uploads' => true,

    /*
     * This is the class that is responsible for naming generated files.
     */
    'file_namer' => Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer::class,

    /*
     * The class that is responsible for naming generated responsive images.
     */
    'responsive_images' => [
        'file_namer' => Spatie\MediaLibrary\ResponsiveImages\ResponsiveImageNamer\DefaultResponsiveImageNamer::class,
    ],

    /*
     * When urls to files get generated, this class will be called. Use the default
     * if your files are stored locally above the site root or on s3.
     */
    'url_generator' => Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator::class,

    /*
     * The class that is responsible for determining the path of an image on the disk.
     */
    'path_generator' => Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator::class,

    /*
     * When dragging and dropping files, the browser may send the files in the wrong order.
     * This setting allows you to configure a time in seconds to wait for all files to arrive.
     */
    'release_new_order_after_seconds' => 5,

    /*
     * When deleting a model, should the related media also be deleted?
     */
    'soft_delete_enabled' => true,
    
    /*
     * FFMPEG & Image Optimizers configuration...
     */
    'ffmpeg_path' => '/usr/bin/ffmpeg',
    'ffprobe_path' => '/usr/bin/ffprobe',

    'jobs' => [
        'perform_conversions' => Spatie\MediaLibrary\Conversions\Jobs\PerformConversionsJob::class,
        'generate_responsive_images' => Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob::class,
    ],
];
