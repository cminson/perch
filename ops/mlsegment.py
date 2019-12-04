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


#
# generate the background region mask
# this is simply the negative of the sum of all other region masks
#
def computeBackgroundRegion(file_name,regionFileList):

    if len(regionFileList) == 0: return

    img = Image.open(regionFileList[0])
    background_bitmap = np.array(img)
    width, height = img.size
    object_name = 'background'
    score = '99'
    x = y = 0

    for regionFile in regionFileList:

        img = Image.open(regionFile)
        bitmap = np.array(img)
        background_bitmap = np.logical_or(background_bitmap, bitmap).astype(np.uint8)

    background_bitmap[background_bitmap == 255] = 0
    background_bitmap[background_bitmap == 0] = 255

    file_name = f'../CONVERSIONS/m{file_name}.{score}.{object_name}.{x}_{y}_{width}_{height}.png'
    img = Image.fromarray(background_bitmap, 'L')
    img.save(file_name, 'PNG')


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

    result = results[0]
    class_ids = result['class_ids']
    masks = result['masks']
    masks = masks.astype(np.uint8)
    scores = result['scores']
    rois = result['rois']

    regionFileList = []
    for index, class_id in enumerate(class_ids):

        object_name = COCO_CLASS_NAMES[class_id].replace(' ', '_')
        score = scores[index]
        score = int(score * 100)
        roi = rois[index]
        print('ROI:', type(roi), roi)

        bitmap = masks[:,:,index]
        bitmap[bitmap > 0] = 255
        print(roi)
        #print(bitmap.shape)

        y1  = roi[0]
        x1 = roi[1]
        y2 = roi[2]
        x2 = roi[3]
        width = x2 - x1
        height = y2 - y1

        outputFileDir = f'../CONVERSIONS/m{file_name}.{score}.{object_name}.{x1}_{y1}_{width}_{height}.png'
        #print(outputFileDir)
        im = Image.fromarray(bitmap, 'L')
        im.save(outputFileDir, 'PNG')

        #logging.debug(f'{outputFileDir}')
        regionFileList.append(outputFileDir)


    computeBackgroundRegion(file_name, regionFileList)


