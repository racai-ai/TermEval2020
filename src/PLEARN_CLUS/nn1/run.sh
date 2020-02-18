#!/bin/sh

python nn1_train.py

python nn1_test.py | tee corp.ann
python nn1_test_equi.py | tee equi.ann
python nn1_test_wind.py | tee wind.ann
