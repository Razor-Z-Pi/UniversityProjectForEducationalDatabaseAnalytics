# retail/models.py
from django.db import models

class ОбобщеннаяГруппаТоваров(models.Model):
    наименование = models.CharField(max_length=100, unique=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        db_table = 'Обощённая_группа_товаров'
        verbose_name = 'Обобщенная группа товаров'
        verbose_name_plural = 'Обобщенные группы товаров'
    
    def __str__(self):
        return self.наименование

class Группа(models.Model):
    наименование = models.CharField(max_length=100)
    id_обобщ_группа = models.ForeignKey(
        ОбобщеннаяГруппаТоваров, 
        on_delete=models.RESTRICT,
        db_column='id_обобщ_группа'
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        db_table = 'Группа'
        verbose_name = 'Группа'
        verbose_name_plural = 'Группы'
        unique_together = ['наименование', 'id_обобщ_группа']
    
    def __str__(self):
        return f"{self.наименование} ({self.id_обобщ_группа})"

class Артикулы(models.Model):
    артикул = models.CharField(max_length=50, unique=True)
    наименование = models.CharField(max_length=200)
    id_группы = models.ForeignKey(
        Группа,
        on_delete=models.RESTRICT,
        db_column='id_группы'
    )
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        db_table = 'Артикулы'
        verbose_name = 'Артикул'
        verbose_name_plural = 'Артикулы'
        unique_together = ['наименование', 'id_группы']
    
    def __str__(self):
        return f"{self.артикул} - {self.наименование}"

class ЕдиницыИзмерения(models.Model):
    наименование = models.CharField(max_length=50, unique=True)
    сокращение = models.CharField(max_length=10, blank=True, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        db_table = 'ед_измерения'
        verbose_name = 'Единица измерения'
        verbose_name_plural = 'Единицы измерения'
    
    def __str__(self):
        return f"{self.наименование} ({self.сокращение})" if self.сокращение else self.наименование

class Города(models.Model):
    город = models.CharField(max_length=100)
    экономический_район = models.CharField(max_length=100)
    федеральный_округ = models.CharField(max_length=100)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        db_table = 'Города'
        verbose_name = 'Город'
        verbose_name_plural = 'Города'
        unique_together = ['город', 'экономический_район']
    
    def __str__(self):
        return f"{self.город} ({self.федеральный_округ})"

class ТипыКлиентов(models.Model):
    наименование = models.CharField(max_length=100, unique=True)
    приоритет = models.IntegerField(default=0)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        db_table = 'типы_клиентов'
        verbose_name = 'Тип клиента'
        verbose_name_plural = 'Типы клиентов'
    
    def __str__(self):
        return self.наименование

class Скидки(models.Model):
    дата_продажи = models.DateField()
    id_город = models.ForeignKey(Города, on_delete=models.RESTRICT, db_column='id_город')
    id_типы_клиентов = models.ForeignKey(ТипыКлиентов, on_delete=models.RESTRICT, db_column='id_типы_клиентов')
    скидки_по_группе_клиентов = models.DecimalField(max_digits=5, decimal_places=2, default=0.00)
    скидки_по_группе_сумме_покупки = models.DecimalField(max_digits=5, decimal_places=2, default=0.00)
    общая_скидка = models.DecimalField(max_digits=5, decimal_places=2, editable=False)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        db_table = 'Скидки'
        verbose_name = 'Скидка'
        verbose_name_plural = 'Скидки'
    
    def save(self, *args, **kwargs):
        self.общая_скидка = self.скидки_по_группе_клиентов + self.скидки_по_группе_сумме_покупки
        super().save(*args, **kwargs)
    
    def __str__(self):
        return f"Скидка {self.общая_скидка}% от {self.дата_продажи}"

class Клиенты(models.Model):
    дата_продажи = models.DateField()
    номер_клиента = models.CharField(max_length=50)
    id_группа = models.ForeignKey(Группа, on_delete=models.RESTRICT, db_column='id_группа')
    id_ед_измерения = models.ForeignKey(ЕдиницыИзмерения, on_delete=models.RESTRICT, db_column='id_ед_измерения')
    id_артикулы = models.ForeignKey(Артикулы, on_delete=models.RESTRICT, db_column='id_артикулы')
    id_скидки = models.ForeignKey(Скидки, on_delete=models.RESTRICT, db_column='id_скидки')
    цена_за_ед = models.DecimalField(max_digits=12, decimal_places=2)
    количество = models.DecimalField(max_digits=10, decimal_places=3)
    сумма_без_скидки = models.DecimalField(max_digits=15, decimal_places=2, editable=False)
    общая_скидка_процент = models.DecimalField(max_digits=5, decimal_places=2, editable=False)
    сумма_со_скидкой = models.DecimalField(max_digits=15, decimal_places=2, editable=False)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)
    
    class Meta:
        db_table = 'Клиенты'
        verbose_name = 'Клиент (продажа)'
        verbose_name_plural = 'Клиенты (продажи)'
    
    def save(self, *args, **kwargs):
        self.сумма_без_скидки = self.цена_за_ед * self.количество
        self.общая_скидка_процент = self.id_скидки.общая_скидка
        self.сумма_со_скидкой = self.сумма_без_скидки * (1 - self.общая_скидка_процент / 100)
        super().save(*args, **kwargs)
    
    def сумма_скидки(self):
        return self.сумма_без_скидки - self.сумма_со_скидкой
    
    def __str__(self):
        return f"Продажа #{self.id} - {self.номер_клиента} ({self.дата_продажи})"