import datetime
print('test')
print('nächster commit')

#programming task 1: https://www.practicepython.org/exercise/2014/01/29/01-character-input.html
age=input('Wie alt wirst du dieses Jahr?')
x=datetime.datetime.now()
year=x.year
int_age=int(age)
int_goal=100
int_diff=int_goal-int_age
str_diff=str(int_diff)
print('du wirst in ' + str_diff + ' Jahren 100')
print('Das ist dann das Jahr' + str(year+int_diff))

