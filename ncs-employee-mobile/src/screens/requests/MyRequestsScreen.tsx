import React, { useCallback, useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Alert,
  Platform,
  LayoutAnimation,
  UIManager,
  Modal,
  SafeAreaView,
} from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RequestStackParamList } from '../../navigation/RequestStack';
import { passApi } from '../../api/passApi';
import { leaveApi } from '../../api/leaveApi';
import { emolumentApi } from '../../api/emolumentApi';
import type { PassApplicationItem, LeaveApplicationItem, EmolumentItem } from '../../api/types';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

if (Platform.OS === 'android' && UIManager.setLayoutAnimationEnabledExperimental) {
  UIManager.setLayoutAnimationEnabledExperimental(true);
}

type FilterCategory = 'all' | 'pass' | 'leave' | 'emolument';
type FilterStatus = 'all' | 'pending' | 'approved' | 'rejected';

type RequestItem =
  | { type: 'pass'; id: number; title: string; subtitle: string; status: string; date?: string }
  | { type: 'leave'; id: number; title: string; subtitle: string; status: string; date?: string }
  | { type: 'emolument'; id: number; title: string; subtitle: string; status: string; date?: string };

export function MyRequestsScreen() {
  const navigation = useNavigation<NativeStackNavigationProp<RequestStackParamList, 'MyRequests'>>();
  const themeColors = useThemeColor();
  const [categoryFilter, setCategoryFilter] = useState<FilterCategory>('all');
  const [statusFilter, setStatusFilter] = useState<FilterStatus>('all');

  const [showCategoryDropdown, setShowCategoryDropdown] = useState(false);
  const [showStatusDropdown, setShowStatusDropdown] = useState(false);

  const [items, setItems] = useState<RequestItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const [showNewRequestModal, setShowNewRequestModal] = useState(false);

  const load = useCallback(async () => {
    setError(null);
    try {
      const [passRes, leaveRes, emolRes] = await Promise.all([
        passApi.list({ per_page: 50 }),
        leaveApi.list({ per_page: 50 }),
        emolumentApi.myEmoluments(),
      ]);

      const list: RequestItem[] = [];

      if (passRes.success && passRes.data) {
        passRes.data.forEach((p: PassApplicationItem) => {
          list.push({
            type: 'pass',
            id: p.id,
            title: 'Pass Application',
            subtitle: `${p.start_date} to ${p.end_date}`,
            status: p.status,
            date: p.submitted_at,
          });
        });
      }
      if (leaveRes.success && leaveRes.data) {
        leaveRes.data.forEach((l: LeaveApplicationItem) => {
          const typeName = l.leave_type?.name ?? 'Leave Request';
          list.push({
            type: 'leave',
            id: l.id,
            title: typeName,
            subtitle: `${l.start_date} to ${l.end_date}`,
            status: l.status,
            date: l.submitted_at,
          });
        });
      }
      if (emolRes.success && emolRes.data) {
        emolRes.data.forEach((e: EmolumentItem) => {
          list.push({
            type: 'emolument',
            id: e.id,
            title: `Emolument ${e.year}`,
            subtitle: e.status, // Fallback
            status: e.status,
            date: e.submitted_at,
          });
        });
      }

      list.sort((a, b) => {
        const dateA = a.date ? new Date(a.date).getTime() : 0;
        const dateB = b.date ? new Date(b.date).getTime() : 0;
        return dateB - dateA;
      });
      setItems(list);
    } catch {
      setError('Failed to load requests');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      setLoading(true);
      load();
    }, [load])
  );

  const handleCategoryChoice = (cat: FilterCategory) => {
    LayoutAnimation.configureNext(LayoutAnimation.Presets.easeInEaseOut);
    setCategoryFilter(cat);
    setShowCategoryDropdown(false);
  };

  const handleStatusChoice = (stat: FilterStatus) => {
    LayoutAnimation.configureNext(LayoutAnimation.Presets.easeInEaseOut);
    setStatusFilter(stat);
    setShowStatusDropdown(false);
  };

  // Filter items by category AND status
  const filtered = items.filter(i => {
    const matchCategory = categoryFilter === 'all' || i.type === categoryFilter;
    let matchStatus = true;
    if (statusFilter !== 'all') {
      const s = i.status.toUpperCase();
      if (statusFilter === 'pending' && s !== 'PENDING') matchStatus = false;
      if (statusFilter === 'approved' && !(s === 'APPROVED' || s === 'PROCESSED' || s === 'SUCCESSFUL')) matchStatus = false;
      if (statusFilter === 'rejected' && !(s === 'REJECTED' || s === 'FAILED')) matchStatus = false;
    }
    return matchCategory && matchStatus;
  });

  const onNew = () => {
    setShowNewRequestModal(true);
  };

  const onItem = (item: RequestItem) => {
    if (item.type === 'pass') navigation.navigate('PassDetail', { id: item.id });
    else if (item.type === 'leave') navigation.navigate('LeaveDetail', { id: item.id });
    else navigation.navigate('EmolumentDetail', { id: item.id });
  };

  const statusColor = (status: string) => {
    const s = status.toUpperCase();
    if (s === 'APPROVED' || s === 'PROCESSED' || s === 'SUCCESSFUL') return '#16a34a'; // green
    if (s === 'REJECTED' || s === 'FAILED') return '#ef4444'; // red
    if (s === 'PENDING') return '#f59e0b'; // amber
    return themeColors.textSecondary;
  };

  const toCamelCase = (str: string) => {
    if (!str) return str;
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
  };

  const getTypeConfig = (type: string) => {
    if (type === 'pass') return { icon: 'card', bg: '#eff6ff', color: '#10b981' };
    if (type === 'leave') return { icon: 'calendar', bg: '#fef2f2', color: '#f97316' };
    return { icon: 'cash', bg: '#ecfdf5', color: '#3b82f6' };
  };

  const renderItem = ({ item }: { item: RequestItem }) => {
    const color = statusColor(item.status);
    const tConfig = getTypeConfig(item.type);

    return (
      <TouchableOpacity
        style={[styles.row, { backgroundColor: themeColors.surface }]}
        onPress={() => onItem(item)}
        activeOpacity={0.7}
      >
        <View style={[styles.iconBox, { backgroundColor: tConfig.bg }]}>
          <Ionicons name={tConfig.icon as any} size={22} color={tConfig.color} />
        </View>

        <View style={styles.rowMiddle}>
          <Text style={[styles.rowTitle, { color: themeColors.text }]} numberOfLines={1}>
            {item.title}
          </Text>
          <Text style={[styles.rowSubtitle, { color: themeColors.textMuted }]}>
            {item.date ? new Date(item.date).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) : item.subtitle}
          </Text>
        </View>

        <View style={styles.rowRight}>
          <Text style={[styles.rowStatus, { color }]}>{toCamelCase(item.status)}</Text>
        </View>
      </TouchableOpacity>
    );
  };

  if (loading && items.length === 0) {
    return (
      <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
        <ActivityIndicator size="large" color={themeColors.primary} />
      </View>
    );
  }

  const categoryLabel = categoryFilter === 'all' ? 'All Categories' : categoryFilter === 'pass' ? 'Pass' : categoryFilter === 'leave' ? 'Leave' : 'Emolument';
  const statusLabel = statusFilter === 'all' ? 'All Status' : statusFilter === 'pending' ? 'Pending' : statusFilter === 'approved' ? 'Approved' : 'Rejected';

  return (
    <View style={[styles.container, { backgroundColor: '#ffffff' }]}>

      {/* Dual Dropdown Header */}
      <View style={styles.filterSection}>
        <TouchableOpacity style={styles.dropdownBtn} onPress={() => setShowCategoryDropdown(true)}>
          <Text style={styles.dropdownText}>{categoryLabel}</Text>
          <Ionicons name="caret-down" size={12} color="#64748b" style={{ marginLeft: 6 }} />
        </TouchableOpacity>

        <TouchableOpacity style={styles.dropdownBtn} onPress={() => setShowStatusDropdown(true)}>
          <Text style={styles.dropdownText}>{statusLabel}</Text>
          <Ionicons name="caret-down" size={12} color="#64748b" style={{ marginLeft: 6 }} />
        </TouchableOpacity>
      </View>

      {error ? <Text style={[styles.error, { color: themeColors.danger }]}>{error}</Text> : null}

      <FlatList
        data={filtered}
        keyExtractor={(i) => `${i.type}-${i.id}`}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        ListEmptyComponent={<Text style={[styles.empty, { color: themeColors.textMuted }]}>No requests found</Text>}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={() => { setRefreshing(true); load(); }}
            colors={[themeColors.primary]}
            tintColor={themeColors.primary}
          />
        }
      />

      <TouchableOpacity
        style={[styles.fab, { backgroundColor: themeColors.primary }]}
        onPress={onNew}
        activeOpacity={0.8}
      >
        <Ionicons name="add" size={28} color="#fff" />
      </TouchableOpacity>

      {/* Modals for Dropdowns */}
      <Modal visible={showCategoryDropdown} transparent animationType="fade">
        <TouchableOpacity style={styles.modalBg} activeOpacity={1} onPress={() => setShowCategoryDropdown(false)}>
          <SafeAreaView>
            <View style={styles.sheetContent}>
              <Text style={styles.sheetTitle}>Select Category</Text>
              {[
                { k: 'all', l: 'All Categories' },
                { k: 'pass', l: 'Pass' },
                { k: 'leave', l: 'Leave' },
                { k: 'emolument', l: 'Emolument' },
              ].map(t => (
                <TouchableOpacity key={t.k} style={styles.sheetOpt} onPress={() => handleCategoryChoice(t.k as FilterCategory)}>
                  <Text style={[styles.sheetOptText, categoryFilter === t.k && { color: themeColors.primary, fontWeight: '700' }]}>{t.l}</Text>
                  {categoryFilter === t.k && <Ionicons name="checkmark" size={20} color={themeColors.primary} />}
                </TouchableOpacity>
              ))}
            </View>
          </SafeAreaView>
        </TouchableOpacity>
      </Modal>

      <Modal visible={showStatusDropdown} transparent animationType="fade">
        <TouchableOpacity style={styles.modalBg} activeOpacity={1} onPress={() => setShowStatusDropdown(false)}>
          <SafeAreaView>
            <View style={styles.sheetContent}>
              <Text style={styles.sheetTitle}>Select Status</Text>
              {[
                { k: 'all', l: 'All Status' },
                { k: 'pending', l: 'Pending' },
                { k: 'approved', l: 'Approved/Processed' },
                { k: 'rejected', l: 'Rejected/Failed' },
              ].map(t => (
                <TouchableOpacity key={t.k} style={styles.sheetOpt} onPress={() => handleStatusChoice(t.k as FilterStatus)}>
                  <Text style={[styles.sheetOptText, statusFilter === t.k && { color: themeColors.primary, fontWeight: '700' }]}>{t.l}</Text>
                  {statusFilter === t.k && <Ionicons name="checkmark" size={20} color={themeColors.primary} />}
                </TouchableOpacity>
              ))}
            </View>
          </SafeAreaView>
        </TouchableOpacity>
      </Modal>

      {/* New Request Selection Modal */}
      <Modal visible={showNewRequestModal} transparent animationType="slide">
        <TouchableOpacity style={styles.modalBg} activeOpacity={1} onPress={() => setShowNewRequestModal(false)}>
          <SafeAreaView>
            <View style={[styles.sheetContent, { paddingBottom: Platform.OS === 'ios' ? spacing['3xl'] : spacing.xl }]}>
              <View style={styles.modalDragIndicator} />
              <Text style={styles.sheetTitle}>New Application</Text>
              <Text style={styles.sheetSubtitle}>What would you like to apply for?</Text>

              <View style={styles.actionGrid}>
                <TouchableOpacity
                  style={styles.actionCard}
                  onPress={() => { setShowNewRequestModal(false); navigation.navigate('LeaveApply'); }}
                >
                  <View style={[styles.actionIconBg, { backgroundColor: '#fef2f2' }]}>
                    <Ionicons name="calendar-outline" size={28} color="#f97316" />
                  </View>
                  <Text style={styles.actionCardTitle}>Leave</Text>
                  <Text style={styles.actionCardDesc}>Apply for annual, sick, or maternity leave.</Text>
                </TouchableOpacity>

                <TouchableOpacity
                  style={styles.actionCard}
                  onPress={() => { setShowNewRequestModal(false); navigation.navigate('PassApply'); }}
                >
                  <View style={[styles.actionIconBg, { backgroundColor: '#eff6ff' }]}>
                    <Ionicons name="card-outline" size={28} color="#10b981" />
                  </View>
                  <Text style={styles.actionCardTitle}>Pass</Text>
                  <Text style={styles.actionCardDesc}>Request short-term absence (working days, limit by grade).</Text>
                </TouchableOpacity>

                <TouchableOpacity
                  style={[styles.actionCard, { width: '100%', marginTop: spacing.md, flexDirection: 'row', alignItems: 'center' }]}
                  onPress={() => { setShowNewRequestModal(false); navigation.navigate('EmolumentRaise'); }}
                >
                  <View style={[styles.actionIconBg, { backgroundColor: '#ecfdf5', width: 44, height: 44, borderRadius: 22, marginBottom: 0, marginRight: spacing.md }]}>
                    <Ionicons name="cash-outline" size={22} color="#3b82f6" />
                  </View>
                  <View style={{ flex: 1 }}>
                    <Text style={[styles.actionCardTitle, { textAlign: 'left' }]}>Emolument Document</Text>
                    <Text style={[styles.actionCardDesc, { textAlign: 'left' }]}>Submit yearly record</Text>
                  </View>
                  <Ionicons name="chevron-forward" size={20} color="#cbd5e1" />
                </TouchableOpacity>
              </View>
            </View>
          </SafeAreaView>
        </TouchableOpacity>
      </Modal>

    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#ffffff' },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },

  filterSection: {
    paddingHorizontal: spacing.xl,
    paddingTop: spacing.lg,
    paddingBottom: spacing.sm,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-start',
    gap: spacing.md,
    backgroundColor: '#ffffff',
  },
  dropdownBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
    backgroundColor: '#f8f9fa',
  },
  dropdownText: { fontSize: 13, fontWeight: '500', color: '#475569' },

  modalBg: { flex: 1, backgroundColor: 'rgba(0,0,0,0.5)', justifyContent: 'flex-end' },
  sheetContent: { backgroundColor: '#fff', borderTopLeftRadius: 28, borderTopRightRadius: 28, padding: spacing.xl },
  sheetTitle: { fontSize: 20, fontWeight: '800', color: '#0f172a', marginBottom: 4, textAlign: 'center' },
  sheetSubtitle: { fontSize: 14, color: '#64748b', textAlign: 'center', marginBottom: spacing.xl },
  sheetOpt: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: spacing.md, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
  sheetOptText: { fontSize: 16, color: '#334155', fontWeight: '500' },

  modalDragIndicator: { width: 40, height: 4, backgroundColor: '#e2e8f0', borderRadius: 2, alignSelf: 'center', marginBottom: spacing.lg },

  actionGrid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between' },
  actionCard: {
    width: '48%',
    backgroundColor: '#f8faf9',
    borderRadius: 16,
    padding: spacing.md,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#f1f5f9',
  },
  actionIconBg: { width: 56, height: 56, borderRadius: 28, justifyContent: 'center', alignItems: 'center', marginBottom: spacing.sm },
  actionCardTitle: { fontSize: 15, fontWeight: '700', color: '#1e293b', marginBottom: 4, textAlign: 'center' },
  actionCardDesc: { fontSize: 12, color: '#64748b', textAlign: 'center', lineHeight: 16 },

  error: { fontSize: fontSizes.sm, padding: spacing.base, textAlign: 'center' },

  list: { paddingHorizontal: spacing.xl, paddingTop: spacing.md, paddingBottom: 100, backgroundColor: '#ffffff' },
  empty: { fontSize: fontSizes.sm, textAlign: 'center', marginTop: spacing.xl },

  row: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: 16,
    padding: spacing.md,
    marginBottom: spacing.xs,
    backgroundColor: '#fafafa',
  },
  iconBox: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  rowMiddle: { flex: 1, paddingRight: spacing.sm },
  rowTitle: {
    fontSize: 15,
    fontWeight: '600',
    marginBottom: 4,
    color: '#1e293b',
  },
  rowSubtitle: { fontSize: 13, color: '#64748b' },

  rowRight: {
    alignItems: 'flex-end',
    justifyContent: 'center',
  },
  rowStatus: {
    fontSize: 12,
    fontWeight: '600',
    marginTop: 4,
  },

  fab: {
    position: 'absolute',
    bottom: 30,
    right: 30,
    width: 60,
    height: 60,
    borderRadius: 30,
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
  }
});
