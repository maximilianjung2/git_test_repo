import csv

array = []
with open("keys.csv", newline="", encoding="utf-8") as f:
    reader = csv.reader(f)
    for row in reader:
        array.append(row)
x=50
count=0
y=0
for row in array:
    # print('Zeile: ' + str(y))
    int_clicks=0
    content=''
    content=row
    direction=content[0]
    str_pn = direction[0]
    int_clicks=int(direction[1:])
    int_clicks=int_clicks%100
    # print(direction)
    # was passiert wenn wir eine zahl größer 100 haben, dann reicht mein +100 und -100 nicht mehr, ich muss die zahl modulo 100 rechnen
    if(str_pn=='L'):
        x=x-int_clicks
        if(x<0):
            x=x+100
    elif(str_pn=='R'):
        x=x+int_clicks  
        if(x>99):
            x=x-100
    print(x)
    if x==0:
        count=count+1

    y=y+1

print('Anzahl 0: ' + str(count))

