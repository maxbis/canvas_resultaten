# Count submissions per student, count #folders in folder, if >=8 print

import os

def count_directories(directory):
    count = 0
    for item in os.listdir(directory):
        item_path = os.path.join(directory, item)
        if os.path.isdir(item_path) and item[0]=='K': # only count dir beginning with the letter K (Kerntaak)
            count += 1
    return count

def count_directories_all(directory):
    for item in os.listdir(directory):
        incomplete_path=os.path.join(directory,'_incomplete')
        os.makedirs(incomplete_path, exist_ok=True)
        item_path = os.path.join(directory, item)
        base=os.path.basename(item_path)
        count = 0
        if os.path.isdir(item_path):
            count = count_directories(item_path)
            if (count >= 7): # 7 workprocesses, probably complete
                print("%-30s : %d " % (item,count))
            else: # not complete, move folder to _incomplete
                # create to-path for rename (move)
                to=os.path.join(incomplete_path, base)
                os.rename(item_path,to)

    return count

root_directory = "D:\Downloads\dl-canvas2"
count_directories_all(root_directory)
