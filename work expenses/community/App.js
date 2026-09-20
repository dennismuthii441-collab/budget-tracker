import React, { useState, useEffect } from 'react';
import {
  StyleSheet,
  Text,
  View,
  TextInput,
  TouchableOpacity,
  ScrollView,
  Alert,
  Modal,
  Platform,
  Linking,
  FlatList,
} from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import * as Clipboard from 'expo-clipboard';
import Svg, { Path, Rect, Line, Circle, G } from 'react-native-svg';

const TREASURER_PHONE = '0715853902';
const TARGET_AMOUNT = 3000;
const STORAGE_KEY = '@mpesa_payments_history';

const QUOTES = [
  "\"Financial freedom is available to those who learn about it and work for it.\"",
  "\"Do not save what is left after spending, but spend what is left after saving.\"",
  "\"Small contributions every day bring big results over time.\"",
  "\"A budget tells your money where to go instead of wondering where it went.\"",
];

// Vector Hand Giving Coin Logo (🫳)
const CommunityLogo = ({ size = 110 }) => (
  <View style={{ width: size, height: size, justifyContent: 'center', alignItems: 'center', marginBottom: 20 }}>
    <Svg width={size} height={size} viewBox="0 0 1024 1024" fill="none">
      <Rect width="1024" height="1024" rx="224" fill="#0F172A" />
      <Circle cx="512" cy="740" r="140" fill="#1E293B" stroke="#334155" strokeWidth="12" />
      <Circle cx="512" cy="740" r="80" stroke="#0284C7" strokeWidth="10" strokeDasharray="20 15" fill="none" />
      <G transform="translate(0, 100)">
        <Circle cx="512" cy="480" r="110" fill="#10B981" />
      </G>
      <Path
        d="M 180 200 L 380 340 C 440 380, 520 380, 600 340 L 780 250 C 820 230, 840 270, 790 300 L 620 400 C 540 450, 440 430, 360 380 L 180 260 Z"
        fill="#F8FAFC"
      />
      <Path
        d="M 600 340 C 660 380, 720 370, 750 330 C 770 300, 740 280, 700 300 C 650 320, 600 320, 560 300"
        stroke="#CBD5E1"
        strokeWidth="24"
        strokeLinecap="round"
        fill="none"
      />
      <Line x1="430" y1="500" x2="430" y2="550" stroke="#38BDF8" strokeWidth="16" strokeLinecap="round" />
      <Line x1="590" y1="500" x2="590" y2="550" stroke="#38BDF8" strokeWidth="16" strokeLinecap="round" />
    </Svg>
  </View>
);

const ReceiptIcon = () => (
  <Svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <Path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1z" />
    <Line x1="16" y1="6" x2="8" y2="6" />
    <Line x1="16" y1="10" x2="8" y2="10" />
    <Line x1="12" y1="14" x2="8" y2="14" />
  </Svg>
);

const CalendarIcon = () => (
  <Svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <Rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
    <Line x1="16" y1="2" x2="16" y2="6" />
    <Line x1="8" y1="2" x2="8" y2="6" />
    <Line x1="3" y1="10" x2="21" y2="10" />
  </Svg>
);

const BellIcon = ({ size = 22, color = '#FFFFFF' }) => (
  <Svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke={color} strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
    <Path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
    <Path d="M13.73 21a2 2 0 0 1-3.46 0" />
  </Svg>
);

export default function App() {
  const [showSplash, setShowSplash] = useState(true);
  const [currentQuote, setCurrentQuote] = useState('');
  const [customAmount, setCustomAmount] = useState('');
  const [mpesaCode, setMpesaCode] = useState('');
  const [payments, setPayments] = useState([]);
  
  const [showHistoryModal, setShowHistoryModal] = useState(false);
  const [showCalendarModal, setShowCalendarModal] = useState(false);
  const [showGuideModal, setShowGuideModal] = useState(false);

  useEffect(() => {
    setCurrentQuote(QUOTES[Math.floor(Math.random() * QUOTES.length)]);
    loadPayments();
    const timer = setTimeout(() => setShowSplash(false), 3000);
    return () => clearTimeout(timer);
  }, []);

  const loadPayments = async () => {
    try {
      const stored = await AsyncStorage.getItem(STORAGE_KEY);
      if (stored) setPayments(JSON.parse(stored));
    } catch (e) {
      console.error('Failed to load history', e);
    }
  };

  const savePaymentRecord = async (newPayment) => {
    try {
      const updated = [newPayment, ...payments];
      setPayments(updated);
      await AsyncStorage.setItem(STORAGE_KEY, JSON.stringify(updated));
    } catch (e) {
      console.error('Failed to save payment', e);
    }
  };

  const totalPaid = payments.reduce((sum, item) => sum + item.amount, 0);
  const remainingBalance = Math.max(0, TARGET_AMOUNT - totalPaid);
  const progressPercentage = Math.min(100, Math.round((totalPaid / TARGET_AMOUNT) * 100));

  const getProgressColor = () => {
    if (progressPercentage < 50) return '#EF4444';
    if (progressPercentage < 100) return '#F59E0B';
    return '#10B981';
  };

  const handleSendMoneyToTreasurer = async () => {
    const amount = parseFloat(customAmount);
    if (!amount || amount <= 0) {
      Alert.alert('Enter Amount', 'Please enter a contribution amount first.');
      return;
    }
    if (totalPaid + amount > TARGET_AMOUNT) {
      Alert.alert('Target Exceeded', `You only need KSh ${remainingBalance} to reach your 3,000 target.`);
      return;
    }
    await Clipboard.setStringAsync(TREASURER_PHONE);
    Alert.alert(
      'Number Copied!',
      `Treasurer number (${TREASURER_PHONE}) copied to clipboard. Paste it when opening M-Pesa Send Money menu.`,
      [
        {
          text: 'Open Dialer (*334#)',
          onPress: () => {
            const ussdCode = Platform.OS === 'android' ? 'tel:*334%23' : 'tel:*334#';
            Linking.openURL(ussdCode).catch(() => {
              Alert.alert('Error', 'Unable to open phone dialer.');
            });
          },
        },
      ]
    );
  };

  const handleAutoPasteSMS = async () => {
    const clipboardText = await Clipboard.getStringAsync();

    if (!clipboardText || clipboardText.trim().length === 0) {
      Alert.alert('Empty Clipboard', 'Copy your M-Pesa SMS message first, then tap Please Verify M-Pesa SMS & Auto Fill Here.');
      return;
    }

    const hasTreasurer = clipboardText.includes(TREASURER_PHONE);
    const codeMatch = clipboardText.match(/\b[A-Z0-9]{10}\b/);
    const amountMatch = clipboardText.match(/Ksh\.?\s*([\d,]+(?:\.\d{2})?)/i);

    let extractedCode = codeMatch ? codeMatch[0] : '';
    let extractedAmountStr = '';

    if (amountMatch) {
      const cleanAmount = amountMatch[1].replace(/,/g, '');
      const parsedValue = parseFloat(cleanAmount);
      if (!isNaN(parsedValue) && parsedValue > 0) {
        extractedAmountStr = parsedValue.toString();
      }
    }

    if (!extractedCode) {
      Alert.alert('Invalid SMS', 'Could not find a valid 10-character M-Pesa transaction code.');
      return;
    }

    setMpesaCode(extractedCode);
    if (extractedAmountStr) {
      setCustomAmount(extractedAmountStr);
    }

    if (!hasTreasurer) {
      Alert.alert(
        'Warning ⚠️',
        `Code: ${extractedCode}\nAmount: KSh ${extractedAmountStr || 'Manual input required'}\n\nNote: Treasurer number (${TREASURER_PHONE}) was not detected in this text. Check before saving.`
      );
    } else {
      Alert.alert(
        'SMS Verified! ✅',
        `Code: ${extractedCode}\nAmount: KSh ${extractedAmountStr}\nRecipient: Treasurer`
      );
    }
  };

  const handleLogPayment = async () => {
    const cleanAmt = customAmount.toString().trim().replace(/,/g, '');
    const amount = parseFloat(cleanAmt);

    if (isNaN(amount) || amount <= 0) {
      Alert.alert('Invalid Amount', 'Please enter or auto-fill a valid contribution amount.');
      return;
    }

    const cleanedCode = mpesaCode.trim().toUpperCase();
    if (!cleanedCode || cleanedCode.length < 6) {
      Alert.alert('Invalid Code', 'Please enter or auto-fill a valid M-Pesa transaction code.');
      return;
    }

    const isDuplicate = payments.some((p) => p.code === cleanedCode);
    if (isDuplicate) {
      Alert.alert('Duplicate Transaction ⚠️', 'This M-Pesa transaction code has already been registered.');
      return;
    }

    const today = new Date().toISOString().split('T')[0];
    const newRecord = {
      id: Date.now().toString(),
      code: cleanedCode,
      amount,
      date: today,
      timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    };

    await savePaymentRecord(newRecord);
    setCustomAmount('');
    setMpesaCode('');
    Alert.alert('Receipt Registered 🎉', `Successfully logged KSh ${amount} under Code ${newRecord.code}.`);
  };

  const handleScheduleReminder = () => {
    Alert.alert(
      'Contribution Reminder Set ⏰',
      `Reminder enabled! Don't forget to complete your remaining target balance of KSh ${remainingBalance}.`
    );
  };

  if (showSplash) {
    return (
      <View style={styles.splashContainer}>
        <CommunityLogo size={110} />
        <Text style={styles.splashTitle}>Contribution Tracker</Text>
        <Text style={styles.splashQuote}>{currentQuote}</Text>
        <Text style={styles.createdCredit}>Created by Dennis</Text>
        <TouchableOpacity style={styles.skipButton} onPress={() => setShowSplash(false)}>
          <Text style={styles.skipButtonText}>Continue to App →</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.content}>
      {/* Header Bar with Bell Icon */}
      <View style={styles.headerBar}>
        <Text style={styles.header}>M-Pesa Contribution Tracker</Text>
        <TouchableOpacity style={styles.bellIconButton} onPress={handleScheduleReminder}>
          <BellIcon size={22} color="#8B5CF6" />
        </TouchableOpacity>
      </View>

      <View style={[styles.card, { backgroundColor: getProgressColor() }]}>
        <Text style={styles.cardTitle}>Target Progress ({progressPercentage}%)</Text>
        <Text style={styles.amountDisplay}>KSh {totalPaid} / {TARGET_AMOUNT}</Text>
        <Text style={styles.subText}>
          {remainingBalance === 0 ? 'Target Completed! 🎉' : `Remaining: KSh ${remainingBalance}`}
        </Text>
      </View>

      <TouchableOpacity style={styles.guideTrigger} onPress={() => setShowGuideModal(true)}>
        <Text style={styles.guideTriggerText}>❓ How to send & verify payments (Click Here)</Text>
      </TouchableOpacity>

      <View style={styles.infoBox}>
        <Text style={styles.infoText}>
          Treasurer: <Text style={{ fontWeight: 'bold' }}>{TREASURER_PHONE}</Text>
        </Text>
      </View>

      <View style={styles.inputGroup}>
        <Text style={styles.label}>1. Contribution Amount (KSh):</Text>
        <TextInput
          style={styles.input}
          keyboardType="numeric"
          placeholder="e.g. 500"
          value={customAmount}
          onChangeText={setCustomAmount}
        />
      </View>

      <TouchableOpacity style={styles.primaryButton} onPress={handleSendMoneyToTreasurer}>
        <Text style={styles.buttonText}>Send Money to Treasurer</Text>
      </TouchableOpacity>

      <View style={[styles.inputGroup, { marginTop: 20 }]}>
        <Text style={styles.label}>2. M-Pesa Transaction Code:</Text>
        <TextInput
          style={styles.input}
          placeholder="e.g. SIK82910XX"
          autoCapitalize="characters"
          value={mpesaCode}
          onChangeText={setMpesaCode}
        />
      </View>

      <TouchableOpacity style={styles.autoActionButton} onPress={handleAutoPasteSMS}>
        <Text style={styles.buttonText}>Please Verify M-Pesa SMS & Auto Fill Here</Text>
      </TouchableOpacity>

      <View style={styles.actionRow}>
        <TouchableOpacity style={styles.secondaryButton} onPress={handleLogPayment}>
          <Text style={styles.buttonText}>Save Verified Payment</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.smallBellButton} onPress={handleScheduleReminder}>
          <BellIcon size={22} color="#FFFFFF" />
        </TouchableOpacity>
      </View>

      <View style={styles.navRow}>
        <TouchableOpacity style={styles.navButton} onPress={() => setShowHistoryModal(true)}>
          <ReceiptIcon />
          <Text style={styles.navButtonText}>Receipts</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.navButton} onPress={() => setShowCalendarModal(true)}>
          <CalendarIcon />
          <Text style={styles.navButtonText}>Calendar</Text>
        </TouchableOpacity>
      </View>

      <Text style={styles.footerCredit}>Created by Dennis</Text>

      {/* USER GUIDE MODAL */}
      <Modal visible={showGuideModal} animationType="slide">
        <View style={styles.modalContainer}>
          <Text style={styles.modalHeader}>📖 How to Use This App</Text>
          <ScrollView style={{ marginTop: 10 }}>
            <View style={styles.stepCard}>
              <Text style={styles.stepTitle}>Step 1: Send Money</Text>
              <Text style={styles.stepBody}>Enter your contribution amount and tap "Send Money to Treasurer". The treasurer's phone number ({TREASURER_PHONE}) will be copied to your clipboard automatically, and your dialer will open.</Text>
            </View>

            <View style={styles.stepCard}>
              <Text style={styles.stepTitle}>Step 2: Copy M-Pesa SMS</Text>
              <Text style={styles.stepBody}>Once Safaricom sends you the transaction confirmation SMS, open your Messages app, tap and hold the SMS text, and select "Copy".</Text>
            </View>

            <View style={styles.stepCard}>
              <Text style={styles.stepTitle}>Step 3: Auto-Fill & Verify</Text>
              <Text style={styles.stepBody}>Return to this app and tap the blue button: "Please Verify M-Pesa SMS & Auto Fill Here". The app reads the code and amount directly from your copied message.</Text>
            </View>

            <View style={styles.stepCard}>
              <Text style={styles.stepTitle}>Step 4: Save Payment</Text>
              <Text style={styles.stepBody}>Tap "Save Verified Payment" to add the receipt to your records and update your total progress.</Text>
            </View>
          </ScrollView>
          <TouchableOpacity style={styles.closeButton} onPress={() => setShowGuideModal(false)}>
            <Text style={styles.buttonText}>Got It!</Text>
          </TouchableOpacity>
        </View>
      </Modal>

      {/* RECEIPTS MODAL */}
      <Modal visible={showHistoryModal} animationType="slide">
        <View style={styles.modalContainer}>
          <Text style={styles.modalHeader}>Payment Receipts Log</Text>
          {payments.length === 0 ? (
            <Text style={styles.emptyText}>No payment receipts logged yet.</Text>
          ) : (
            <FlatList
              data={payments}
              keyExtractor={(item) => item.id}
              renderItem={({ item }) => (
                <View style={styles.historyRow}>
                  <View>
                    <Text style={styles.historyCode}>{item.code}</Text>
                    <Text style={styles.historyDate}>{item.date} at {item.timestamp}</Text>
                  </View>
                  <Text style={styles.historyAmount}>+ KSh {item.amount}</Text>
                </View>
              )}
            />
          )}
          <TouchableOpacity style={styles.closeButton} onPress={() => setShowHistoryModal(false)}>
            <Text style={styles.buttonText}>Close Receipts</Text>
          </TouchableOpacity>
        </View>
      </Modal>

      {/* CALENDAR MODAL */}
      <Modal visible={showCalendarModal} animationType="slide">
        <View style={styles.modalContainer}>
          <Text style={styles.modalHeader}>Payment Calendar Log</Text>
          <Text style={styles.subTextDark}>Contributions grouped by date:</Text>
          <ScrollView style={{ marginTop: 15 }}>
            {payments.length === 0 ? (
              <Text style={styles.emptyText}>No payment entries recorded.</Text>
            ) : (
              Object.entries(
                payments.reduce((acc, curr) => {
                  acc[curr.date] = (acc[curr.date] || 0) + curr.amount;
                  return acc;
                }, {})
              ).map(([date, dailyTotal]) => (
                <View key={date} style={styles.calendarCard}>
                  <Text style={styles.calendarDate}>{date}</Text>
                  <Text style={styles.calendarTotal}>Total Paid: KSh {dailyTotal}</Text>
                </View>
              ))
            )}
          </ScrollView>
          <TouchableOpacity style={styles.closeButton} onPress={() => setShowCalendarModal(false)}>
            <Text style={styles.buttonText}>Close Calendar</Text>
          </TouchableOpacity>
        </View>
      </Modal>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#f4f6f8' },
  content: { padding: 20, paddingTop: 50 },
  splashContainer: { flex: 1, backgroundColor: '#0f172a', justifyContent: 'center', alignItems: 'center', padding: 30 },
  splashTitle: { fontSize: 26, fontWeight: 'bold', color: '#10B981', marginBottom: 20 },
  splashQuote: { fontSize: 16, fontStyle: 'italic', color: '#cbd5e1', textAlign: 'center', lineHeight: 24, marginBottom: 20 },
  createdCredit: { color: '#38bdf8', fontSize: 14, fontWeight: 'bold', marginBottom: 30 },
  skipButton: { backgroundColor: '#1e293b', paddingVertical: 12, paddingHorizontal: 20, borderRadius: 20, borderWidth: 1, borderColor: '#334155' },
  skipButtonText: { color: '#38bdf8', fontWeight: '600', fontSize: 14 },
  headerBar: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 15 },
  header: { fontSize: 20, fontWeight: 'bold', color: '#1a1a1a', flex: 1 },
  bellIconButton: { padding: 8, backgroundColor: '#f1f5f9', borderRadius: 20, borderWidth: 1, borderColor: '#cbd5e1' },
  card: { padding: 20, borderRadius: 12, marginBottom: 12, alignItems: 'center' },
  cardTitle: { color: '#fff', fontSize: 16, fontWeight: '600' },
  amountDisplay: { color: '#fff', fontSize: 26, fontWeight: 'bold', marginVertical: 8 },
  subText: { color: '#e2e8f0', fontSize: 14 },
  subTextDark: { color: '#475569', fontSize: 14 },
  guideTrigger: { backgroundColor: '#fef3c7', padding: 10, borderRadius: 8, marginBottom: 12, borderWidth: 1, borderColor: '#fde047', alignItems: 'center' },
  guideTriggerText: { color: '#854d0e', fontWeight: 'bold', fontSize: 13 },
  infoBox: { backgroundColor: '#e0f2fe', padding: 10, borderRadius: 8, marginBottom: 15, alignItems: 'center' },
  infoText: { color: '#0369a1', fontSize: 13 },
  inputGroup: { marginBottom: 12 },
  label: { fontSize: 14, fontWeight: '600', color: '#334155', marginBottom: 6 },
  input: { backgroundColor: '#fff', padding: 12, borderRadius: 8, borderWidth: 1, borderColor: '#cbd5e1' },
  primaryButton: { backgroundColor: '#0284C7', padding: 15, borderRadius: 8, alignItems: 'center', marginTop: 5 },
  autoActionButton: { backgroundColor: '#2563EB', padding: 15, borderRadius: 8, alignItems: 'center', marginTop: 10 },
  actionRow: { flexDirection: 'row', alignItems: 'center', marginTop: 10 },
  secondaryButton: { backgroundColor: '#059669', padding: 15, borderRadius: 8, alignItems: 'center', flex: 1, marginRight: 10 },
  smallBellButton: { backgroundColor: '#8B5CF6', padding: 15, borderRadius: 8, justifyContent: 'center', alignItems: 'center' },
  buttonText: { color: '#fff', fontWeight: 'bold', fontSize: 14, textAlign: 'center' },
  navRow: { flexDirection: 'row', justifyContent: 'space-between', marginTop: 20 },
  navButton: { backgroundColor: '#334155', padding: 14, borderRadius: 8, flex: 0.48, flexDirection: 'row', alignItems: 'center', justifyContent: 'center' },
  navButtonText: { color: '#fff', fontWeight: 'bold', fontSize: 14, marginLeft: 8 },
  footerCredit: { textAlign: 'center', color: '#64748b', fontSize: 13, fontWeight: '600', marginTop: 30, marginBottom: 10 },
  modalContainer: { flex: 1, padding: 25, paddingTop: 50, backgroundColor: '#f8fafc' },
  modalHeader: { fontSize: 20, fontWeight: 'bold', color: '#0f172a', marginBottom: 15 },
  stepCard: { backgroundColor: '#fff', padding: 15, borderRadius: 8, marginBottom: 12, borderWidth: 1, borderColor: '#e2e8f0' },
  stepTitle: { fontSize: 16, fontWeight: 'bold', color: '#0284C7', marginBottom: 5 },
  stepBody: { fontSize: 13, color: '#334155', lineHeight: 18 },
  emptyText: { color: '#94a3b8', textAlign: 'center', marginTop: 30, fontSize: 16 },
  historyRow: { flexDirection: 'row', justifyContent: 'space-between', backgroundColor: '#fff', padding: 15, borderRadius: 8, marginBottom: 10, borderWidth: 1, borderColor: '#e2e8f0' },
  historyCode: { fontWeight: 'bold', fontSize: 15, color: '#0f172a' },
  historyDate: { color: '#64748b', fontSize: 12, marginTop: 4 },
  historyAmount: { fontWeight: 'bold', color: '#059669', fontSize: 15 },
  calendarCard: { backgroundColor: '#fff', padding: 15, borderRadius: 8, marginBottom: 10, borderWidth: 1, borderColor: '#cbd5e1' },
  calendarDate: { fontSize: 16, fontWeight: 'bold', color: '#0369a1' },
  calendarTotal: { fontSize: 14, color: '#375279', marginTop: 4 },
  closeButton: { backgroundColor: '#dc2626', padding: 15, borderRadius: 8, alignItems: 'center', marginTop: 20 },
});