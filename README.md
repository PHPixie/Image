# Image

PHPixie Image library

[![Author](http://img.shields.io/badge/author-@dracony-blue.svg?style=flat-square)](https://twitter.com/dracony)
[![Source Code](http://img.shields.io/badge/source-phpixie/image-blue.svg?style=flat-square)](https://github.com/phpixie/image)
[![Software License](https://img.shields.io/badge/license-BSD-brightgreen.svg?style=flat-square)](https://github.com/phpixie/orm/blob/master/LICENSE)

There are multiple libraries for image manipulation in PHP, unfortunately their API is not always intuitive.
PHPixie Image provides a common simple interface to working with GD, Imagick and Gmagick making development easy
and allowing effortless switching between them.

## Initializing

Add `"phpixie/image":"~3.0"` to your composer.json and run `composer update`.

```php
$image = new \PHPixie\Image();

//You can also specify which driver to use ('gd', 'imagick' or 'gmagick')
//With 'gd' being the default one
$image = new \PHPixie\Image('gmagick');
```

- **GD** – The most used PHP imaging library. Good enough for most cases, but is slower than the rest and may not offer the same level of image quality.
- **Imagick** – PHP wrapper for the popular ImageMagick library. Offers solid performance and perfect quality but requires a PHP extension.
- **Gmagick** – PHP GraphicsMagick bindings. Faster than Imagick, but the recent API changes sometimes cause unpredictable results when working with text.

PHPixie framework users have the Image component already present and can be accessed via:

```php
$image = $builder->components()->image();
```

The default drivers can be specified in the configuration file:

```php
// /assets/config/image.php
return array(
    'defaultDriver' => 'imagick'
);
```

# Creating and reading images

Now that you have the library initialized, you can create a new image or read one from disk:

```php
// Create a new 100x200 white image
$img = $image->create(100, 200);

// Create a new 100x200 image filled with red color at 0.5 opacity
$img = $image->create(100, 200, 0xff0000, 0.5);

// Read a file from disk
$img = $image->read('/some/file.png');

// Load a file from file contents
$data = file_get_contents('/some/file.png');
$img = $image->load($data);
```

The above examples will all use the default driver you specified when initializing the library.
If you find yourself working with multiple backends at once you can specify the driver to use for each image:

```php
$driver = $image->driver('imagick');
$img = $driver->create(100, 200, 0xff0000, 0.5);
$img = $driver->read('/some/file.png');
$img = $driver->load($data);

//Or using an optional parameter
$img = $image->create(100, 200, 0xff0000, 0.5, 'imagick');
$img = $image->read('/some/file.png', 'imagick');
$img = $image->load($data, 'imagick');
```

We'll take a look at image manipulation in a second, but first let's see how we can write the images back to disk or
render them into a variable.

```php
// Save to file
// By default Image will guess the image format
// from the file name
$img->save('pixie.png');

// You can always specify the format and quality manually though
$img->save('pixie.jpg', 'jpg', 90);

// This will render the image data into a variable,
// useful for sending directly to the browser
$data = $img->render('png');

// This method also supports quality specification
$data = $img->render('jpg', 90);
```

# Image manipulation

**Resizing and Cropping**

```php
// Resize it to 400px width, aspect ratio is maintained
$img->resize(400);

// Resize to 200px in height
$img->resize(null, 200);

// Resize to fit in a 200x100 box
// A 300x300 image would become 100x100
// it's as if you specify the maximum size
$img->resize(200, 100);

// Resize to "fill" a 200x100 box
// A 300x300 image would become 200x200
// it's as if you specify the minumum size
$img->resize(200, 100, false);

//Scale image using a ratio
//This would make it twice as big
$img->scale(2);

//Crop image to 100x150 with 10 horizontal offset
//and 15 vertical
$img->crop(100, 100, 10, 15);
```

If you would like to have user avatars of a fixed size, you would have to resize them to be as close to the needed size as possible and then crop them, like this:

```php
//Let's assume $img is 300x200
//and we want to make 100x100 avatars.

//Note how you can chain the methods together
$img->resize(100, 100, false) //becomes 150x100
	->crop(100, 100)
	->save('avatar.png');

//We even have a predefined fill() function for this =)
$img->fill(100, 100)->save('avatar.png'); //that's it
```

**Rotating and flipping**

```php
//Rotate the image 45 degrees counter clockwise
//filling the background with semitransparent white
$img->rotate(45, 0xffffff, 0.5);

$img->flip(true); //flip horizontally
$img->flip(false, true); //flip vertically
$img->flip(true, true); //flip bloth
```

**Overlaying Images**
Overlaying is most useful for watermarking images or creating some fancy avatars. You can overlay any number of images by chaining _overlay()_ calls:

```php
$meadow = $image->read('meadow.png');
$fairy  = $image->read('fairy.png');
$flower = $image->read('flower.png');

//Put fairy at coordinates 40, 50
$meadow->overlay($fairy, 40, 50)
	->overlay($flower, 100, 200)
	->save('meadow2.png');
```

Note that overlaying an image will not auto expand the existing one, meaning that if you overlay a 500×300 image over a 100×100 one you will get a 100×100 result with the access cropped out. You can work around this by creating a canvas layer:

```php
$large = $image->read('large.png');// 500x300
$small = $image->read('small.png');// 100x100

//Make transparent canvas the size of large image
$canvas = $image->create($large->width(), $large->height();
$canvas->overlay($small)
	->overlay($large)
	->save('merged.png');
```

**Drawing Text**

Drawing text is one of the most frustrating things in image manipulation, especially when it comes to wrapping text
over multiple lines. Before we look at examples, let's take a look at font metrics.

 ![Font Metrics](https://phpixie.com/images/blog/2013/07/Typography_Line_Terms.svg_.png)


When specifying text coordinates we will be specifying the coordinates of the *baseline*, so the text will appear slightly higher.

```php
//Make white background
$img = $this->pixie->create(500, 500, 0xffffff, 1);

//Write "tinkerbell" using font.ttf font and font size 30
//Put it in coordinates 50, 60 (baseline coordinates)
//And make it half transparent red color
$img->text("Tinkerbell", 30, '/path/font.ttf', 50, 60, 0xff0000, 0.5);

//Wrap text so that its 200 pixel wide
$text = "Trixie is a nice little fairy that like spicking flowers";
$img->text($text, 30, '/path/font.ttf', 50, 60, 0xff0000, 0.5, 200);

//Increase Line spacing by 50%
$img->text($text, 30, '/path/font.ttf', 50, 60, 0xff0000, 0.5, 200, 1.5);

//Write text under a 45 degree counter clockwise angle:
$img->text("Tinkerbell", 30, '/path/font.ttf', 50, 60, 0xff0000, 0.5, null, 1, 45);
```

That’s it. You should be able to cope with pretty much any image now.
