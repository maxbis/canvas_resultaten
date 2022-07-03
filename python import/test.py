from threading import Thread
from pprint import pprint
import time
import random

def dummy(a,b,c, results, ii):
    results[ii]=a*b+c
    time.sleep(random.randint(1,3))
    return True

threads = []

items=[1,2,3,4,5,6,7,8,9,10]
results = [{} for x in items]
pprint(results)

for i in range(10):
    process = Thread(target=dummy, args=[i, 2, i, results, i])
    process.start()
    threads.append(process)

for process in threads:
    process.join()

pprint(results)