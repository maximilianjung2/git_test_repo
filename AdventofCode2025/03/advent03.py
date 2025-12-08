import csv
array = []
with open("puzzle.csv", newline="", encoding="utf-8") as f:
    reader = csv.reader(f)
    for row in reader:
        array.append(row)
#print(array)
sum=0
for row in array:
        #print(row)
        battery=str(row)
        # gehe in die einzelne Zeile
        stelle=0
        highest=1
        #finde die erste ziffer
        for ziffer in battery:
            #print(ziffer)
            stelle=stelle+1
            #print('Stelle: ' + str(stelle))
            ziffer2=stelle 
            #finde die 2. Ziffer indem du immer an der Stelle der ersten Ziffer s
            for  ziffer2 in battery[stelle:]:
                #print('Ziffer2: ' + str(ziffer2))
                if (ziffer + ziffer2).isdigit():
                    gesamt= str(ziffer) + str(ziffer2)
                    if (int(gesamt) > highest):
                        highest=int(gesamt)
        sum=int(highest)+sum
print(sum)     








