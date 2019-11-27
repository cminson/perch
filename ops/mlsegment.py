#!/usr/bin/python3

# Author:  Christopher Minson
#
#
#
import os
import sys
import random
import math
import numpy as np
import skimage.io
import matplotlib
import matplotlib.pyplot as plt
from PIL import Image
import logging
LOG_FILENAME = '/var/www/perch/ops/python.log'
logging.basicConfig(filename=LOG_FILENAME,level=logging.DEBUG)

import warnings
warnings.filterwarnings("ignore")

MODEL_PATH = '/var/www/perch/MODELS/mask_rcnn/mask_rcnn_coco.hy'
MODEL_WEIGHTS_PATH = '/var/www/perch/MODELS/mask_rcnn/mask_rcnn_coco.h5'
MODEL_DIR = '/var/www/perch/MODELS/Mask_RCNN'
COCO_DIR = '/var/www/perch/MODELS/Mask_RCNN/samples/coco'

COCO_CLASS_NAMES = ['BG', 'person', 'bicycle', 'car', 'motorcycle', 'airplane',
               'bus', 'train', 'truck', 'boat', 'traffic light',
               'fire hydrant', 'stop sign', 'parking meter', 'bench', 'bird',
               'cat', 'dog', 'horse', 'sheep', 'cow', 'elephant', 'bear',
               'zebra', 'giraffe', 'backpack', 'umbrella', 'handbag', 'tie',
               'suitcase', 'frisbee', 'skis', 'snowboard', 'sports ball',
               'kite', 'baseball bat', 'baseball glove', 'skateboard',
               'surfboard', 'tennis racket', 'bottle', 'wine glass', 'cup',
               'fork', 'knife', 'spoon', 'bowl', 'banana', 'apple',
               'sandwich', 'orange', 'broccoli', 'carrot', 'hot dog', 'pizza',
               'donut', 'cake', 'chair', 'couch', 'potted plant', 'bed',
               'dining table', 'toilet', 'tv', 'laptop', 'mouse', 'remote',
               'keyboard', 'cell phone', 'microwave', 'oven', 'toaster',
               'sink', 'refrigerator', 'book', 'clock', 'vase', 'scissors',
               'teddy bear', 'hair drier', 'toothbrush']

if __name__ == '__main__':

    count = len(sys.argv)
    if count != 2:
        print('ERROR', end='')
        exit()

    inputFileDir = sys.argv[1]
    file_name =  os.path.basename(inputFileDir).split('.')[0]
    #print(file_name)

    # Locally import Mask RCNN and coco
    sys.path.append(MODEL_DIR)
    sys.path.append(COCO_DIR)
    from mrcnn import utils
    import mrcnn.model as modellib
    import coco

    class InferenceConfig(coco.CocoConfig):
        # Set batch size to 1 since we'll be running inference on
        # one image at a time. Batch size = GPU_COUNT * IMAGES_PER_GPU
        GPU_COUNT = 1
        IMAGES_PER_GPU = 1

    config = InferenceConfig()
    #config.display()

    # Create model object in inference mode, fold in weights
    model = modellib.MaskRCNN(mode="inference", model_dir=MODEL_PATH, config=config)
    model.load_weights(MODEL_WEIGHTS_PATH, by_name=True)

    image = skimage.io.imread(inputFileDir)
    results = model.detect([image], verbose=1)

    logging.debug(f'RESULTS: {len(results)}')

    """
    r['rois'], r['masks'], r['class_ids'], class_names, r['scores'])
    always one result.
    print(COCO_CLASS_NAMES)
    print(r['class_ids'])
    print(r['scores'])
    """
    logging.debug(f'HERE1')

    result = results[0]
    class_ids = result['class_ids']
    masks = result['masks']
    masks = masks.astype(np.uint8)
    scores = result['scores']

    for index, class_id in enumerate(class_ids):

        object_name = COCO_CLASS_NAMES[class_id].replace(' ', '_')
        score = scores[index]
        score = int(score * 100)

        bitmap = masks[:,:,index]
        bitmap[bitmap > 0] = 255
        #print(bitmap.shape)

        logging.debug('HERE2')
        outputFileDir = f'../CONVERSIONS/m{file_name}.{object_name}.{score}.png'
        #print(outputFileDir)
        im = Image.fromarray(bitmap, 'L')
        im.save(outputFileDir, 'PNG')

        logging.debug(f'{object_name} {score}')



