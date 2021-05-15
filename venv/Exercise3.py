#https://www.practicepython.org/exercise/2014/02/15/03-list-less-than-ten.html
list=[]
result_list_positive=[]
result_list_negative=[]
x=0
divisor=1
list_anzahl=1
list_anzahl=int(input('Wie viele Zahlen willst du prüfen?'))
divisor= int(input('Gib den Divisor ein'))
while x<4:
    list.append(int(input('Gebe eine Zahl ein')))
    x=x+1
for element in list:
    if element % divisor == 0:
        result_list_positive.append(element)
    else:
        result_list_negative.append(element)
print('teilbar durch: '+str(divisor))
print(result_list_positive)
###bugfix
print('folgende liste ist nicht teilbar durch: '+str(divisor))
print(result_list_negative)


