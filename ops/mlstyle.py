#!/usr/bin/python3

# Author:  Christopher Minson
# Article: https://www.christopherminson.com/articles/artvideo.html
#
# Interpret an image with given style input 
#
#
import os
import sys
from random import randint

import numpy as np
import matplotlib.image 
import tensorflow as tf
import tensorflow_hub as hub

MAX_IMAGE_DIM = 1024
PATH_OUTPUT = '/var/www/christopherminson/ic/conversions/'

#
# normalize an image for usage by dnn
#
def load_image(path_image):

    image = tf.io.read_file(path_image)
    image = tf.image.decode_image(image, channels=3)
    image = tf.image.convert_image_dtype(image, tf.float32)

    shape = tf.cast(tf.shape(image)[:-1], tf.float32)
    long_dim = max(shape)
    scale = MAX_IMAGE_DIM / long_dim

    new_shape = tf.cast(shape * scale, tf.int32)

    image = tf.image.resize(image, new_shape)
    image = image[tf.newaxis, :]
    return image


os.environ["TFHUB_CACHE_DIR"] = '/var/www/christopherminson/ic/cache'


if __name__ == '__main__':

    count = len(sys.argv)
    if count != 3:
        print('ERROR')
        exit()

    name_original = sys.argv[1]
    name_style = sys.argv[2]

    hub_handle = 'https://tfhub.dev/google/magenta/arbitrary-image-stylization-v1-256/1'
    hub_module = hub.load(hub_handle)

    image_original = load_image(name_original);
    image_style = load_image(name_style);

    results = hub_module(tf.constant(image_original), tf.constant(image_style))

    image = tf.squeeze(results[0], axis=0)

    output_name = str(randint(100000,999999)) + '.jpg'
    matplotlib.image.imsave(PATH_OUTPUT + output_name, image)

    print(f'result: {PATH_OUTPUT + output_name}', end='')

