# imagemachine

ImageMachine is a small PHP image store and server. 

## Goal/Reuirements

* __Storing:__ Accept a URL as an image source then provide a token to address the cached/stored image.
* __Reading:__ Accept a token to provide an image in a specified resolution and/or format.


Some secondary features required:
* The read API should provide a method to crop and resize images.
* For the purposes of my project, I require the machine to...
  * accept JPEG, GIF, PNG, and SVG
  * only serve JPG's

This image store facility is only going to be used by an automated robot, so no pretty human interface is required.


## Basic API format
* __Storing:__ /store/secret-key/base64_encoded_URL
* __Reading:__ /key/token/size

## Storing an Image
Calling the API with parameters something like this:

![http://localhost/imagemachine/store/123/aHR0cHM6Ly91cGxvYWQud2lraW1lZGlhLm9yZy93aWtpcGVkaWEvY29tbW9ucy9iL2IwL05ld1R1eC5zdmc=](http://4.bp.blogspot.com/-tGMs7M2VSu8/UxS_LKR3VEI/AAAAAAAAAd0/hiQC68tFmh8/s1600/save.png "Store Image")

Where __store__ is the action, __123__ is the secret key to store images, and the last parameter is the [base64 encoded](http://www.base64encode.org/) result of "[https://upload.wikimedia.org/wikipedia/commons/b/b0/NewTux.svg](https://upload.wikimedia.org/wikipedia/commons/b/b0/NewTux.svg)"
Should result in JSON looking something like this:

```javascript
{
	status: "ok",
	msg: "stored",
	guid: "de683d6b2e298de8e831b2f632132269"
}
```

The above means that the imageserver has decoded the URL, downloaded it, saved it in JPEG format (configurable), and returned a key for you to address that image in the future. The key is simply a hash of the URL passed in.

## Reading an Image
Calling the API with parameters something like this (using the token from above):

![http://localhost/imagemachine/~/de683d6b2e298de8e831b2f632132269](http://3.bp.blogspot.com/-ExUF-gT3hRo/UxS9EN4eyeI/AAAAAAAAAdg/tTYRDZQu8c0/s1600/store1.PNG)

Will return an image in the default size and cropping. 

![](http://1.bp.blogspot.com/-c5uKQqmG-Kk/UxWClxiaQ8I/AAAAAAAAAeI/waVYU2cvQWc/s1600/m.jpg)

The read key in this example is simply set to a tilde (~) as security for reading images out of this store is of no concern. To specify a size/cropping scheme, append one of the predefined sizes as another parameter:

![http://localhost/imagemachine/~/de683d6b2e298de8e831b2f632132269/s](http://2.bp.blogspot.com/-gnhEz56-Rn4/UxS9EvGboAI/AAAAAAAAAdk/g49WGKBglfw/s1600/store2.PNG)

Where __s__ has been setup as a "small" version of the image.

![](http://3.bp.blogspot.com/-FG6MjMjn3lY/UxWCmM-WNZI/AAAAAAAAAeE/EBIvvZ_6Ems/s1600/s.jpg)

## My Experience on a Hosted Solution
I use the [Grid Service](http://mediatemple.net/webhosting/shared/) product offered by [MediaTemple](http://mediatemple.net/) for my hosting. I followed [this article](http://stackoverflow.com/questions/18519609/imagemagick-installation-mediatemple-gridserver) to get the [ImageMagick PECL](http://pecl.php.net/package/imagick) working. But ended up discovering that the extension was quite limited compared to the native console <b>convert</b>. So I fell back to using [PHP's exec](http://php.net/function.exec).

## License

(The MIT License)

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
'Software'), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
