import { FontAwesome5, Ionicons, MaterialCommunityIcons } from '@expo/vector-icons';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as Print from 'expo-print';
import * as Sharing from 'expo-sharing';
import { useEffect, useState } from 'react';
import {
    Alert,
    Keyboard,
    KeyboardAvoidingView,
    Platform,
    SafeAreaView,
    ScrollView,
    StatusBar,
    StyleSheet,
    Text,
    TextInput,
    TouchableOpacity,
    TouchableWithoutFeedback,
    View,
} from 'react-native';

const CATEGORIES = [
  { id: '1', name: 'Food', icon: 'silverware-fork-knife', color: '#EA4335' },
  { id: '2', name: 'Transport', icon: 'car-side', color: '#4285F4' },
  { id: '3', name: 'Housing', icon: 'home-variant', color: '#FBBC04' },
  { id: '4', name: 'Shopping', icon: 'shopping-bag', color: '#34A853' },
  { id: '5', name: 'Bills', icon: 'lightning-bolt', color: '#A142F4' },
];

const STORAGE_KEY_EXPENSES = '@expenses_tracker_data_v2';
const STORAGE_KEY_REMINDERS = '@budget_reminders_data_v2';
const STORAGE_KEY_BUDGET = '@monthly_budget_cap';

export default function App() {
  const [expenses, setExpenses] = useState([]);
  const [reminders, setReminders] = useState([]);
  const [budgetCap, setBudgetCap] = useState('50000');
  const [isEditingBudget, setIsEditingBudget] = useState(false);

  // Expense Form State
  const [title, setTitle] = useState('');
  const [amount, setAmount] = useState('');
  const [selectedCategory, setSelectedCategory] = useState(CATEGORIES[0]);
  const [isRecurring, setIsRecurring] = useState(false);

  // Reminder State
  const [reminderText, setReminderText] = useState('');
  const [isDarkMode, setIsDarkMode] = useState(true);

  useEffect(() => {
    loadData();
  }, []);

  useEffect(() => {
    saveData();
  }, [expenses, reminders, budgetCap]);

  const loadData = async () => {
    try {
      const storedExpenses = await AsyncStorage.getItem(STORAGE_KEY_EXPENSES);
      const storedReminders = await AsyncStorage.getItem(STORAGE_KEY_REMINDERS);
      const storedBudget = await AsyncStorage.getItem(STORAGE_KEY_BUDGET);

      if (storedExpenses) setExpenses(JSON.parse(storedExpenses));
      if (storedReminders) setReminders(JSON.parse(storedReminders));
      if (storedBudget) setBudgetCap(storedBudget);
    } catch (e) {
      Alert.alert('Error', 'Failed to load local data.');
    }
  };

  const saveData = async () => {
    try {
      await AsyncStorage.setItem(STORAGE_KEY_EXPENSES, JSON.stringify(expenses));
      await AsyncStorage.setItem(STORAGE_KEY_REMINDERS, JSON.stringify(reminders));
      await AsyncStorage.setItem(STORAGE_KEY_BUDGET, budgetCap);
    } catch (e) {
      Alert.alert('Error', 'Failed to save data.');
    }
  };

  const addExpense = () => {
    if (!title.trim()) {
      Alert.alert('Missing Field', 'Please enter an expense title.');
      return;
    }
    const parsedAmount = parseFloat(amount);
    if (isNaN(parsedAmount) || parsedAmount <= 0) {
      Alert.alert('Invalid Amount', 'Please enter a valid amount.');
      return;
    }

    const newExpense = {
      id: Date.now().toString(),
      title: title.trim(),
      amount: parsedAmount,
      category: selectedCategory.name,
      categoryIcon: selectedCategory.icon,
      categoryColor: selectedCategory.color,
      isRecurring: isRecurring,
      date: new Date().toLocaleDateString('en-KE', { day: 'numeric', month: 'short', year: 'numeric' }),
    };

    setExpenses([newExpense, ...expenses]);
    setTitle('');
    setAmount('');
    setIsRecurring(false);
    Keyboard.dismiss();
  };

  const deleteExpense = (id) => {
    Alert.alert('Delete Transaction', 'Are you sure you want to delete this record?', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Delete',
        style: 'destructive',
        onPress: () => setExpenses(expenses.filter((item) => item.id !== id)),
      },
    ]);
  };

  // Generate PDF Statement
  const exportPDFReceipt = async () => {
    if (expenses.length === 0) {
      Alert.alert('Empty Data', 'No transactions available to generate receipt.');
      return;
    }

    const htmlContent = `
      <html>
        <head>
          <style>
            body { font-family: Helvetica, sans-serif; padding: 20px; color: #333; }
            h1 { color: #1A73E8; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            th { background-color: #f2f2f2; }
            .total { font-weight: bold; color: #EA4335; font-size: 18px; margin-top: 20px; }
          </style>
        </head>
        <body>
          <h1>Budget Planner - Monthly Statement</h1>
          <p>Generated on: ${new Date().toLocaleString()}</p>
          <p class="total">Total Spent: KES ${totalSpent.toLocaleString()}</p>
          <table>
            <tr>
              <th>Date</th>
              <th>Title</th>
              <th>Category</th>
              <th>Amount (KES)</th>
            </tr>
            ${expenses
              .map(
                (item) => `
              <tr>
                <td>${item.date}</td>
                <td>${item.title} ${item.isRecurring ? '(Recurring)' : ''}</td>
                <td>${item.category}</td>
                <td>KES ${item.amount.toLocaleString()}</td>
              </tr>
            `
              )
              .join('')}
          </table>
        </body>
      </html>
    `;

    try {
      const { uri } = await Print.printToFileAsync({ html: htmlContent });
      await Sharing.shareAsync(uri);
    } catch (error) {
      Alert.alert('Error', 'Could not generate PDF receipt.');
    }
  };

  // Reminder Actions
  const addReminder = () => {
    if (!reminderText.trim()) return;
    setReminders([...reminders, { id: Date.now().toString(), text: reminderText.trim(), completed: false }]);
    setReminderText('');
    Keyboard.dismiss();
  };

  const toggleReminder = (id) => {
    setReminders(reminders.map((r) => (r.id === id ? { ...r, completed: !r.completed } : r)));
  };

  const deleteReminder = (id) => {
    setReminders(reminders.filter((r) => r.id !== id));
  };

  const totalSpent = expenses.reduce((sum, item) => sum + Number(item.amount || 0), 0);
  const parsedCap = parseFloat(budgetCap) || 1;
  const progressPercent = Math.min((totalSpent / parsedCap) * 100, 100);

  const theme = {
    bg: isDarkMode ? '#121212' : '#F8F9FA',
    card: isDarkMode ? '#1E1E1E' : '#FFFFFF',
    textPrimary: isDarkMode ? '#E8EAED' : '#202124',
    textSecondary: isDarkMode ? '#9AA0A6' : '#5F6368',
    primary: isDarkMode ? '#8AB4F8' : '#1A73E8',
    cardBorder: isDarkMode ? '#2C2C2C' : '#E0E0E0',
    inputBg: isDarkMode ? '#28292A' : '#F1F3F4',
  };

  return (
    <SafeAreaView style={[styles.container, { backgroundColor: theme.bg }]}>
      <StatusBar barStyle={isDarkMode ? 'light-content' : 'dark-content'} backgroundColor={theme.bg} />

      {/* Top Bar */}
      <View style={styles.topBar}>
        <View style={styles.brandContainer}>
          <View style={styles.slantedIconContainer}>
            <FontAwesome5 name="wallet" size={24} color={theme.primary} />
          </View>
          <Text style={[styles.appTitle, { color: theme.textPrimary }]}>Budget Planner</Text>
        </View>
        <TouchableOpacity
          style={[styles.themeToggle, { backgroundColor: theme.inputBg }]}
          onPress={() => setIsDarkMode(!isDarkMode)}
        >
          <Ionicons name={isDarkMode ? 'sunny-outline' : 'moon-outline'} size={20} color={theme.textPrimary} />
        </TouchableOpacity>
      </View>

      <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : 'height'} style={{ flex: 1 }}>
        <TouchableWithoutFeedback onPress={Keyboard.dismiss}>
          <ScrollView contentContainerStyle={{ paddingHorizontal: 16, paddingBottom: 30 }}>
            {/* Wallet Card with Cap & Progress Bar */}
            <View style={[styles.walletCard, { backgroundColor: isDarkMode ? '#1E293B' : '#1A73E8' }]}>
              <View style={styles.walletCardHeader}>
                <Text style={styles.walletCardLabel}>Monthly Spending</Text>
                <TouchableOpacity onPress={() => setIsEditingBudget(!isEditingBudget)}>
                  <Ionicons name="settings-outline" size={20} color="#FFFFFF" />
                </TouchableOpacity>
              </View>

              <Text style={styles.walletCardAmount}>KES {totalSpent.toLocaleString()}</Text>

              {/* Monthly Cap Setting */}
              {isEditingBudget ? (
                <View style={styles.capInputRow}>
                  <Text style={{ color: '#FFF' }}>Limit: KES </Text>
                  <TextInput
                    style={styles.capInput}
                    keyboardType="numeric"
                    value={budgetCap}
                    onChangeText={setBudgetCap}
                    onBlur={() => setIsEditingBudget(false)}
                  />
                </View>
              ) : (
                <Text style={styles.capText}>Monthly Cap: KES {parsedCap.toLocaleString()}</Text>
              )}

              {/* Dynamic Progress Bar */}
              <View style={styles.progressTrack}>
                <View
                  style={[
                    styles.progressFill,
                    {
                      width: `${progressPercent}%`,
                      backgroundColor: progressPercent > 85 ? '#EA4335' : progressPercent > 60 ? '#FBBC04' : '#34A853',
                    },
                  ]}
                />
              </View>

              <View style={styles.bgWalletIcon}>
                <FontAwesome5 name="wallet" size={130} color="rgba(255, 255, 255, 0.12)" />
              </View>
            </View>

            {/* Export Receipt Banner */}
            <TouchableOpacity style={styles.exportButton} onPress={exportPDFReceipt}>
              <Ionicons name="document-text-outline" size={20} color="#FFF" />
              <Text style={styles.exportText}>Export Statement PDF / Receipt</Text>
            </TouchableOpacity>

            {/* Reminders Card */}
            <View style={[styles.formCard, { backgroundColor: theme.card, borderColor: theme.cardBorder }]}>
              <View style={styles.sectionHeaderRow}>
                <Ionicons name="notifications-outline" size={20} color={theme.primary} />
                <Text style={[styles.formHeader, { color: theme.textPrimary, marginBottom: 0 }]}>Budget Reminders</Text>
              </View>

              <View style={styles.reminderInputRow}>
                <TextInput
                  style={[styles.input, { flex: 1, backgroundColor: theme.inputBg, color: theme.textPrimary, marginBottom: 0 }]}
                  placeholder="Add item (e.g., Pay Wifi)"
                  placeholderTextColor={theme.textSecondary}
                  value={reminderText}
                  onChangeText={setReminderText}
                />
                <TouchableOpacity style={[styles.addReminderBtn, { backgroundColor: theme.primary }]} onPress={addReminder}>
                  <Ionicons name="add" size={22} color="#FFF" />
                </TouchableOpacity>
              </View>

              {reminders.map((item) => (
                <View key={item.id} style={[styles.reminderItem, { backgroundColor: theme.inputBg }]}>
                  <TouchableOpacity onPress={() => toggleReminder(item.id)} style={styles.reminderLeft}>
                    <Ionicons
                      name={item.completed ? 'checkmark-circle' : 'ellipse-outline'}
                      size={22}
                      color={item.completed ? '#34A853' : theme.textSecondary}
                    />
                    <Text
                      style={[
                        styles.reminderText,
                        { color: theme.textPrimary },
                        item.completed && { textDecorationLine: 'line-through', color: theme.textSecondary },
                      ]}
                    >
                      {item.text}
                    </Text>
                  </TouchableOpacity>
                  <TouchableOpacity onPress={() => deleteReminder(item.id)}>
                    <Ionicons name="trash-outline" size={18} color="#EA4335" />
                  </TouchableOpacity>
                </View>
              ))}
            </View>

            {/* Add Expense Card */}
            <View style={[styles.formCard, { backgroundColor: theme.card, borderColor: theme.cardBorder }]}>
              <Text style={[styles.formHeader, { color: theme.textPrimary }]}>New Transaction</Text>

              <TextInput
                style={[styles.input, { backgroundColor: theme.inputBg, color: theme.textPrimary }]}
                placeholder="Transaction Title"
                placeholderTextColor={theme.textSecondary}
                value={title}
                onChangeText={setTitle}
              />

              <TextInput
                style={[styles.input, { backgroundColor: theme.inputBg, color: theme.textPrimary }]}
                placeholder="Amount (KES)"
                placeholderTextColor={theme.textSecondary}
                keyboardType="numeric"
                value={amount}
                onChangeText={setAmount}
              />

              {/* Recurring Switch */}
              <TouchableOpacity
                style={styles.recurringRow}
                onPress={() => setIsRecurring(!isRecurring)}
              >
                <Ionicons
                  name={isRecurring ? 'checkbox' : 'square-outline'}
                  size={20}
                  color={isRecurring ? theme.primary : theme.textSecondary}
                />
                <Text style={{ color: theme.textPrimary, fontSize: 13 }}>Repeat Monthly (Recurring Expense)</Text>
              </TouchableOpacity>

              <Text style={[styles.categoryHeader, { color: theme.textSecondary }]}>Select Category</Text>
              <View style={styles.categoryRow}>
                {CATEGORIES.map((cat) => (
                  <TouchableOpacity
                    key={cat.id}
                    style={[
                      styles.categoryChip,
                      { backgroundColor: theme.inputBg },
                      selectedCategory.name === cat.name && { backgroundColor: theme.primary },
                    ]}
                    onPress={() => setSelectedCategory(cat)}
                  >
                    <MaterialCommunityIcons
                      name={cat.icon}
                      size={24}
                      color={selectedCategory.name === cat.name ? '#FFF' : cat.color}
                    />
                    <Text
                      style={[
                        styles.chipText,
                        { color: theme.textPrimary },
                        selectedCategory.name === cat.name && { color: '#FFF', fontWeight: 'bold' },
                      ]}
                    >
                      {cat.name}
                    </Text>
                  </TouchableOpacity>
                ))}
              </View>

              <TouchableOpacity style={[styles.submitButton, { backgroundColor: theme.primary }]} onPress={addExpense}>
                <Ionicons name="add" size={22} color="#FFF" />
                <Text style={styles.submitText}>Save Expense</Text>
              </TouchableOpacity>
            </View>

            {/* Transactions History */}
            <Text style={[styles.listHeader, { color: theme.textPrimary }]}>Transactions History</Text>
            {expenses.map((item) => (
              <View key={item.id} style={[styles.expenseRow, { backgroundColor: theme.card, borderColor: theme.cardBorder }]}>
                <View style={styles.leftRow}>
                  <View style={[styles.iconBox, { backgroundColor: item.categoryColor + '20' }]}>
                    <MaterialCommunityIcons name={item.categoryIcon} size={28} color={item.categoryColor} />
                  </View>
                  <View>
                    <Text style={[styles.itemTitle, { color: theme.textPrimary }]}>
                      {item.title} {item.isRecurring && '🔄'}
                    </Text>
                    <Text style={[styles.itemSub, { color: theme.textSecondary }]}>
                      {item.category} • {item.date}
                    </Text>
                  </View>
                </View>

                <View style={styles.rightActionRow}>
                  <Text style={styles.itemAmount}>- KES {Number(item.amount).toLocaleString()}</Text>
                  <TouchableOpacity onPress={() => deleteExpense(item.id)} style={styles.deleteIconButton}>
                    <Ionicons name="trash-outline" size={20} color="#EA4335" />
                  </TouchableOpacity>
                </View>
              </View>
            ))}
          </ScrollView>
        </TouchableWithoutFeedback>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  topBar: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 16 },
  brandContainer: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  slantedIconContainer: { transform: [{ rotate: '-15deg' }] },
  appTitle: { fontSize: 22, fontWeight: 'bold' },
  themeToggle: { padding: 8, borderRadius: 20 },

  walletCard: { padding: 20, borderRadius: 24, marginBottom: 14, position: 'relative', overflow: 'hidden', elevation: 4 },
  walletCardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
  walletCardLabel: { color: '#FFFFFF', fontSize: 14, fontWeight: '600', opacity: 0.9 },
  walletCardAmount: { color: '#FFFFFF', fontSize: 32, fontWeight: 'bold', marginTop: 6, zIndex: 2 },
  capText: { color: '#FFFFFF', fontSize: 12, opacity: 0.8, marginTop: 2, marginBottom: 8 },
  capInputRow: { flexDirection: 'row', alignItems: 'center', marginVertical: 4 },
  capInput: { backgroundColor: 'rgba(255,255,255,0.2)', color: '#FFF', paddingHorizontal: 8, borderRadius: 6, fontSize: 12 },
  progressTrack: { height: 8, backgroundColor: 'rgba(255,255,255,0.3)', borderRadius: 4, overflow: 'hidden', marginTop: 6 },
  progressFill: { height: '100%', borderRadius: 4 },
  bgWalletIcon: { position: 'absolute', right: -20, bottom: -35, transform: [{ rotate: '-25deg' }] },

  exportButton: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 8, backgroundColor: '#34A853', padding: 12, borderRadius: 14, marginBottom: 16 },
  exportText: { color: '#FFF', fontWeight: 'bold', fontSize: 14 },

  formCard: { padding: 16, borderRadius: 20, borderWidth: 1, marginBottom: 20 },
  sectionHeaderRow: { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 12 },
  formHeader: { fontSize: 16, fontWeight: '600', marginBottom: 12 },
  input: { padding: 12, borderRadius: 12, fontSize: 14, marginBottom: 10 },

  reminderInputRow: { flexDirection: 'row', gap: 8, marginBottom: 12 },
  addReminderBtn: { width: 46, height: 46, borderRadius: 12, justifyContent: 'center', alignItems: 'center' },
  reminderItem: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 10, borderRadius: 10, marginTop: 6 },
  reminderLeft: { flexDirection: 'row', alignItems: 'center', gap: 8, flex: 1 },
  reminderText: { fontSize: 14 },

  recurringRow: { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 12 },
  categoryHeader: { fontSize: 13, fontWeight: '600', marginBottom: 10 },
  categoryRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 10, marginBottom: 16 },
  categoryChip: { flexDirection: 'row', alignItems: 'center', gap: 8, paddingHorizontal: 14, paddingVertical: 10, borderRadius: 24 },
  chipText: { fontSize: 13 },
  submitButton: { flexDirection: 'row', alignItems: 'center', justifyContent: 'center', gap: 6, padding: 14, borderRadius: 14 },
  submitText: { color: '#FFF', fontWeight: 'bold', fontSize: 15 },
  listHeader: { fontSize: 17, fontWeight: 'bold', marginBottom: 12 },
  expenseRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', padding: 14, borderRadius: 18, borderWidth: 1, marginBottom: 10 },
  leftRow: { flexDirection: 'row', alignItems: 'center', gap: 14, flex: 1 },
  iconBox: { width: 50, height: 50, borderRadius: 16, justifyContent: 'center', alignItems: 'center' },
  itemTitle: { fontSize: 15, fontWeight: '600' },
  itemSub: { fontSize: 12, marginTop: 2 },
  rightActionRow: { flexDirection: 'row', alignItems: 'center', gap: 10 },
  itemAmount: { color: '#EA4335', fontWeight: 'bold', fontSize: 14 },
  deleteIconButton: { padding: 4 },
});